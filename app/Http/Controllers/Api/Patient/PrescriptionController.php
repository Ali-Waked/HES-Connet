<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Patient;

use App\Enums\FacilityType;
use App\Enums\MedicationRequestStatus;
use App\Enums\PrescriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Prescription\SelectPharmacyRequest;
use App\Http\Resources\AvailablePharmacyResource;
use App\Http\Resources\PrescriptionCollection;
use App\Http\Resources\PrescriptionResource;
use App\Models\Facility;
use App\Models\MedicationRequest;
use App\Models\PharmacyMedicine;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function index(Request $request): PrescriptionCollection
    {
        $patient = $request->user()->patient;
        $patientId = $patient?->id;

        abort_unless($patientId, 403, __('Patient profile not found.'));

        $prescriptions = Prescription::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.facilityStaff.staff.user', 'items.medicine'])
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return new PrescriptionCollection($prescriptions);
    }

    public function show(Request $request, Prescription $prescription): JsonResponse
    {
        $patient = $request->user()->patientProfile;
        $patientId = $patient?->id;

        abort_unless($patientId, 403, __('Patient profile not found.'));

        abort_unless(
            $prescription->appointment?->patient_id === $patientId,
            403,
            __('This prescription does not belong to you.')
        );

        $prescription->load(['appointment.facilityStaff.staff.user', 'items.medicine']);

        return response()->json([
            'data' => new PrescriptionResource($prescription),
        ]);
    }

    public function availablePharmacies(Request $request, Prescription $prescription)
    {
        $patient = $request->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));

        abort_unless(
            $prescription->appointment?->patient_id === $patient->id,
            403,
            __('This prescription does not belong to you.')
        );

        $prescriptionItems = $prescription->items()
            ->get(['medicine_id', 'quantity']);

        if ($prescriptionItems->isEmpty()) {

            return AvailablePharmacyResource::collection(collect());
        }

        $medicineIds = $prescriptionItems
            ->pluck('medicine_id')
            ->unique();
        info($medicineIds);

        $totalItems = $prescriptionItems->count();

        $pharmacies = Facility::query()
            ->where('facility_type', FacilityType::PHARMACY)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->with([
                'pharmacyMedicines' => fn ($query) => $query
                    ->available()
                    ->whereIn('medicine_id', $medicineIds)
                    ->select([
                        'id',
                        'facility_id',
                        'medicine_id',
                        'stock',
                        'price',
                    ]),
            ])
            ->get([
                'id',
                'uuid',
                'name',
            ])
            ->map(function (Facility $pharmacy) use ($prescriptionItems, $totalItems) {
                info('why');
                $availableItems = $prescriptionItems
                    ->filter(function ($item) use ($pharmacy) {
                        $medicine = $pharmacy->pharmacyMedicines
                            ->firstWhere('medicine_id', $item->medicine_id);

                        return $medicine?->stock >= $item->quantity;
                    })
                    ->count();
                $totalPrice = 0;

                foreach ($prescriptionItems as $item) {

                    $medicine = $pharmacy->pharmacyMedicines
                        ->firstWhere('medicine_id', $item->medicine_id);
                    info($medicine);

                    if ($medicine) {
                        $totalPrice += $medicine->price * $item->quantity;
                    }
                }
                $pharmacy->total_price = $totalPrice;
                $pharmacy->available_items = $availableItems;
                $pharmacy->total_items = $totalItems;
                $pharmacy->coverage_percentage = (int) round(
                    ($availableItems / $totalItems) * 100
                );
                $pharmacy->can_fulfill = $availableItems === $totalItems;

                return $pharmacy;
            })
            ->sortByDesc('coverage_percentage')
            ->sortByDesc('can_fulfill')
            ->values();
        info($pharmacies);

        return AvailablePharmacyResource::collection($pharmacies);
    }

    public function selectPharmacy(
        SelectPharmacyRequest $request,
        Prescription $prescription
    ): JsonResponse {
        $patient = $request->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));

        abort_unless(
            $prescription->appointment?->patient_id === $patient->id,
            403,
            __('This prescription does not belong to you.')
        );
        // if($prescription->status == PrescriptionStatus::PHARMACY_SELECTED)
        // {
        //     return $prescription->pharmchy
        // }
        abort_unless(
            $prescription->status === PrescriptionStatus::ACTIVE,
            422,
            __('Prescription is not available for pharmacy selection.')
        );

        $facility = Facility::query()
            ->where('uuid', $request->input('facility_id'))
            ->where('facility_type', FacilityType::PHARMACY)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->firstOrFail();

        $prescriptionItems = $prescription->items()
            ->get(['medicine_id', 'quantity']);

        $availableMedicines = PharmacyMedicine::query()
            ->available()
            ->where('facility_id', $facility->id)
            ->whereIn('medicine_id', $prescriptionItems->pluck('medicine_id'))
            ->get()
            ->keyBy('medicine_id');

        $canFulfill = $prescriptionItems->every(function ($item) use ($availableMedicines) {
            $medicine = $availableMedicines->get($item->medicine_id);

            return $medicine
                && $medicine->stock >= $item->quantity;
        });

        abort_unless(
            $canFulfill,
            422,
            __('The selected pharmacy cannot fulfill this prescription.')
        );

        $medicationRequest = DB::transaction(function () use (
            $prescription,
            $patient,
            $facility
        ) {
            $prescription->update([
                'status' => PrescriptionStatus::PHARMACY_SELECTED,
            ]);

            return MedicationRequest::create([
                'patient_id' => $patient->id,
                'facility_id' => $facility->id,
                'prescription_id' => $prescription->id,
                'status' => MedicationRequestStatus::PENDING,
            ]);
        });
        info($medicationRequest->uuid);

        return response()->json([
            'message' => __('Pharmacy selected successfully.'),
            'data' => [
                'medication_request_uuid' => $medicationRequest->uuid,
            ],
        ], 201);
    }
}
