<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\FacilityOwner;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicationRequestResource;
use App\Http\Resources\PrescriptionCollection;
use App\Http\Resources\PrescriptionResource;
use App\Models\Facility;
use App\Models\MedicationRequest;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    private function getFacility(Request $request): Facility
    {
        $user = $request->user();

        if ($user->hasSystemRole('super_admin') && $request->has('facility_id')) {
            return Facility::query()
                ->where('uuid', $request->input('facility_id'))
                ->orWhere('id', $request->input('facility_id'))
                ->firstOrFail();
        }

        $facility = Facility::query()
            ->where('created_by', $user->id)
            ->first();

        abort_unless($facility, 403, __('No facility found for this user.'));

        return $facility;
    }

    public function prescriptions(Request $request): PrescriptionCollection
    {
        $facility = $this->getFacility($request);

        $prescriptions = Prescription::query()
            ->whereHas('appointment', fn ($q) => $q->where('facility_id', $facility->id))
            ->with(['appointment.facilityStaff.staff.user', 'items.medicine', 'appointment.patient.user'])
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return new PrescriptionCollection($prescriptions);
    }

    public function medicationRequests(Request $request): JsonResponse
    {
        $facility = $this->getFacility($request);

        $requests = MedicationRequest::query()
            ->where('facility_id', $facility->id)
            ->with(['patient.user', 'facility', 'prescription', 'pharmacist.user'])
            ->when(
                $request->get('status'),
                fn ($q, $status) => $q->where('status', $status)
            )
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => MedicationRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $facility = $this->getFacility($request);

        $totalPrescriptions = Prescription::query()
            ->whereHas('appointment', fn ($q) => $q->where('facility_id', $facility->id))
            ->count();

        $totalRequests = MedicationRequest::query()
            ->where('facility_id', $facility->id)
            ->count();

        $requestsByStatus = MedicationRequest::query()
            ->selectRaw("status, COUNT(*) as count")
            ->where('facility_id', $facility->id)
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentRequests = MedicationRequestResource::collection(
            MedicationRequest::query()
                ->where('facility_id', $facility->id)
                ->with(['patient.user', 'facility', 'prescription', 'pharmacist.user'])
                ->latest()
                ->limit(10)
                ->get()
        );

        return response()->json([
            'data' => [
                'total_prescriptions' => $totalPrescriptions,
                'total_medication_requests' => $totalRequests,
                'requests_by_status' => $requestsByStatus,
                'recent_requests' => $recentRequests,
            ],
        ]);
    }
}
