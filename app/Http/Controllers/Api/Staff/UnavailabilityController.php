<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreUnavailabilityRequest;
use App\Http\Requests\Staff\UpdateUnavailabilityRequest;
use App\Http\Resources\Staff\UnavailabilityResource;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\StaffUnavailability;
use App\Services\StaffUnavailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnavailabilityController extends Controller
{
    public function __construct(
        private readonly StaffUnavailabilityService $unavailabilityService,
    ) {}

    public function index(
        Request $request,
        Facility $facility,
    ): JsonResponse {
        $staff = $request->user()->staff()->firstOrFail();

        $facilityStaff = FacilityStaff::query()
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $unavailabilities = StaffUnavailability::query()
            ->where('facility_staff_id', $facilityStaff->id)
            ->with('facilityStaff.facility')
            ->orderBy('start_at')
            ->get();

        return response()->json([
            'data' => UnavailabilityResource::collection($unavailabilities),
        ]);
    }

    public function store(
        StoreUnavailabilityRequest $request,
        Facility $facility,
    ): JsonResponse {
        $staff = $request->user()->staff()->firstOrFail();

        $facilityStaff = FacilityStaff::query()
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->active()
            ->firstOrFail();

        $data = $request->validated();
        $data['facility_staff_id'] = $facilityStaff->id;

        $unavailability = $this->unavailabilityService->create($data);

        return response()->json([
            'message' => __('Unavailability created successfully.'),
            'data' => new UnavailabilityResource(
                $unavailability->load('facilityStaff.facility')
            ),
        ], 201);
    }

    public function update(
        UpdateUnavailabilityRequest $request,
        Facility $facility,
        StaffUnavailability $unavailability,
    ): JsonResponse {
        $staff = $request->user()->staff()->firstOrFail();

        FacilityStaff::query()
            ->where('id', $unavailability->facility_staff_id)
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $unavailability = $this->unavailabilityService->update(
            $unavailability,
            $request->validated(),
        );

        return response()->json([
            'message' => __('Unavailability updated successfully.'),
            'data' => new UnavailabilityResource(
                $unavailability->load('facilityStaff.facility')
            ),
        ]);
    }

    public function destroy(
        Request $request,
        Facility $facility,
        StaffUnavailability $unavailability,
    ): JsonResponse {
        $staff = $request->user()->staff()->firstOrFail();

        FacilityStaff::query()
            ->where('id', $unavailability->facility_staff_id)
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $this->unavailabilityService->destroy($unavailability);

        return response()->json([
            'message' => __('Unavailability deleted successfully.'),
        ]);
    }
}
