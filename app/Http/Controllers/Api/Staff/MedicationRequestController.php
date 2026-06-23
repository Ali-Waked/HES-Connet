<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Enums\MedicationRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationRequest\AcceptMedicationRequestRequest;
use App\Http\Requests\MedicationRequest\RejectMedicationRequestRequest;
use App\Http\Resources\MedicationRequestResource;
use App\Models\Facility;
use App\Models\MedicationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationRequestController extends Controller
{
    /**
     * Get facility medication requests
     */
    public function index(Request $request, Facility $facility): JsonResponse
    {
        $this->ensureStaffHasFacility($request, $facility);

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

    /**
     * Accept request
     */
    public function acceptRequest(
        AcceptMedicationRequestRequest $request,
        Facility $facility,
        MedicationRequest $medicationRequest
    ): JsonResponse {
        $staff = $this->ensureStaffHasFacility($request, $facility);

        $this->ensureRequestBelongsToFacility($medicationRequest, $facility);

        abort_unless(
            $medicationRequest->status === MedicationRequestStatus::PENDING,
            422,
            __('Only pending requests can be accepted.')
        );

        DB::transaction(function () use ($medicationRequest, $staff, $request) {
            $medicationRequest->update([
                'status' => 'approved',
                'pharmacist_id' => $staff->id,
                'notes' => $request->input('notes', $medicationRequest->notes),
            ]);

            $medicationRequest->prescription->update([
                'status' => 'accepted',
            ]);
        });

        return response()->json([
            'message' => __('Medication request accepted successfully.'),
            'data' => new MedicationRequestResource(
                $medicationRequest->load(['patient.user', 'facility', 'prescription', 'pharmacist.user'])
            ),
        ]);
    }

    /**
     * Reject request
     */
    public function rejectRequest(
        RejectMedicationRequestRequest $request,
        Facility $facility,
        MedicationRequest $medicationRequest
    ): JsonResponse {
        $staff = $this->ensureStaffHasFacility($request, $facility);

        $this->ensureRequestBelongsToFacility($medicationRequest, $facility);

        abort_unless(
            $medicationRequest->status === MedicationRequestStatus::PENDING,
            422,
            __('Only pending requests can be rejected.')
        );

        DB::transaction(function () use ($medicationRequest, $staff, $request) {
            $medicationRequest->update([
                'status' => 'rejected',
                'pharmacist_id' => $staff->id,
                'notes' => $request->input('notes'),
            ]);

            $medicationRequest->prescription->update([
                'status' => 'rejected',
            ]);
        });

        return response()->json([
            'message' => __('Medication request rejected successfully.'),
            'data' => new MedicationRequestResource(
                $medicationRequest->load(['patient.user', 'facility', 'prescription', 'pharmacist.user'])
            ),
        ]);
    }

    /**
     * Ensure staff belongs to facility
     */
    private function ensureStaffHasFacility(Request $request, Facility $facility)
    {
        $staff = $request->user()->staff;

        abort_unless(
            $staff?->facilities()->where('facilities.id', $facility->id)->exists(),
            403,
            __('You do not have access to this facility.')
        );

        return $staff;
    }

    /**
     * Ensure request belongs to facility
     */
    private function ensureRequestBelongsToFacility(MedicationRequest $medicationRequest, Facility $facility): void
    {
        abort_unless(
            $medicationRequest->facility_id === $facility->id,
            403,
            __('This medication request does not belong to this facility.')
        );
    }
}
