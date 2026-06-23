<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Patient;

use App\Enums\MedicationRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\MedicationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicationRequestController extends Controller
{
    /**
     * List patient medication requests
     */
    public function index(Request $request): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));
        $requests = MedicationRequest::query()
            ->where('patient_id', $patient->id)

            // filter by status
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })

            // filter by facility
            ->when($request->filled('facility_id'), function ($query) use ($request) {
                $query->where('facility_id', $request->string('facility_id'));
            })

            // search by pharmacy name
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->whereHas('facility', function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                });
            })

            ->with([
                'facility:id,uuid,name,cover_image',
                'prescription:id,uuid,status',
            ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $requests,
        ]);
    }

    /**
     * Show single medication request
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        info($uuid);
        $patient = $request->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));

        $medicationRequest = MedicationRequest::query()
            ->where('uuid', $uuid)
            ->where('patient_id', $patient->id)
            ->with([
                'facility:id,uuid,name,cover_image,latitude,longitude',
                'prescription.items.medicine',
            ])
            ->firstOrFail();

        return response()->json([
            'data' => $medicationRequest,
        ]);
    }

    public function cancel(string $uuid): JsonResponse
    {
        $patient = request()->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));

        $request = MedicationRequest::query()
            ->where('uuid', $uuid)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        abort_unless(
            $request->status === MedicationRequestStatus::PENDING,
            422,
            __('Only pending requests can be cancelled.')
        );

        $request->update([
            'status' => MedicationRequestStatus::CANCELLED,
        ]);

        return response()->json([
            'message' => __('Medication request cancelled successfully.'),
        ]);
    }
}
