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
use App\Services\UuidResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnavailabilityController extends Controller
{
    public function __construct(
        private readonly StaffUnavailabilityService $unavailability_service,
        private readonly UuidResolver $uuid_resolver,
    ) {}

    public function index(Request $request, string $staff): JsonResponse
    {
        info([$staff, '019ed9e2-6f98-7230-8c83-ba15c3dc223a']);
        $facilityStaffQuery = FacilityStaff::where('staff_id', $staff->id);

        if ($facilityUuid = $request->query('facility_id')) {
            $facilityId = $this->uuid_resolver->resolve(Facility::class, $facilityUuid);
            $facilityStaffQuery->where('facility_id', $facilityId);
        }

        $facilityStaffIds = $facilityStaffQuery->pluck('id');

        $unavailabilities = StaffUnavailability::query()
            ->whereIn('facility_staff_id', $facilityStaffIds)
            ->with('facilityStaff.facility')
            ->orderBy('start_at')
            ->get();

        return response()->json([
            'data' => UnavailabilityResource::collection($unavailabilities),
        ]);
    }

    public function store(StoreUnavailabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $staff = $request->user()->staff()->firstOrFail();

        $facilityId = $this->uuid_resolver->resolve(Facility::class, $validated['facility_id']);
        $facilityStaff = FacilityStaff::query()
            ->where('staff_id', $staff->id)
            ->where('facility_id', $facilityId)
            ->active()
            ->firstOrFail();
        $validated['facility_staff_id'] = $facilityStaff->id;
        // $validated['facility_staff_uuid'] = $facilityStaff->uuid;
        // unset($validated['facility_id']);

        $unavailability = $this->unavailability_service->create($validated);

        return response()->json([
            'message' => __('Unavailability created successfully.'),
            'data' => new UnavailabilityResource($unavailability->load('facilityStaff.facility')),
        ], 201);
    }

    public function update(UpdateUnavailabilityRequest $request, StaffUnavailability $unavailability): JsonResponse
    {
        $staff = $request->user()->staff()->firstOrFail();

        FacilityStaff::query()
            ->where('staff_id', $staff->id)
            ->where('id', $unavailability->facility_staff_id)
            ->firstOrFail();

        $validated = $request->validated();

        if (isset($validated['facility_id'])) {
            $facilityId = $this->uuid_resolver->resolve(Facility::class, $validated['facility_id']);
            $newFacilityStaff = FacilityStaff::query()
                ->where('staff_id', $staff->id)
                ->where('facility_id', $facilityId)
                ->active()
                ->firstOrFail();
            $validated['facility_staff_uuid'] = $newFacilityStaff->uuid;
            unset($validated['facility_id']);
        }

        $unavailability = $this->unavailability_service->update($unavailability, $validated);

        return response()->json([
            'message' => __('Unavailability updated successfully.'),
            'data' => new UnavailabilityResource($unavailability->load('facilityStaff.facility')),
        ]);
    }

    public function destroy(Request $request, StaffUnavailability $unavailability): JsonResponse
    {
        $staff = $request->user()->staff()->firstOrFail();

        FacilityStaff::query()
            ->where('staff_id', $staff->id)
            ->where('id', $unavailability->facility_staff_id)
            ->firstOrFail();

        $this->unavailability_service->destroy($unavailability);

        return response()->json([
            'message' => __('Unavailability deleted successfully.'),
        ]);
    }
}
