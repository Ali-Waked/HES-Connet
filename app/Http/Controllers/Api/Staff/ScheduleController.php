<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreScheduleRequest;
use App\Http\Requests\Staff\UpdateScheduleRequest;
use App\Http\Resources\Staff\ScheduleResource;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Services\ScheduleService;
use App\Services\UuidResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleService $schedule_service,
        private readonly UuidResolver $uuid_resolver,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $staff = $request->user()->staff;
        $facilityStaffQuery = FacilityStaff::where('staff_id', $staff->id);

        if ($facilityUuid = $request->query('facility_id')) {
            $facilityId = $this->uuid_resolver->resolve(Facility::class, $facilityUuid);
            $facilityStaffQuery->where('facility_id', $facilityId);
        }

        $facilityStaffIds = $facilityStaffQuery->pluck('id');

        $schedules = StaffSchedule::query()
            ->whereIn('facility_staff_id', $facilityStaffIds)
            ->with('facilityStaff.facility')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'data' => ScheduleResource::collection($schedules),
        ]);
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $staff = $request->user()->staff()->firstOrFail();

        $facilityId = $this->uuid_resolver->resolve(Facility::class, $validated['facility_uuid']);
        $facilityStaff = FacilityStaff::query()
            ->where('staff_id', $staff->id)
            ->where('facility_id', $facilityId)
            ->active()
            ->firstOrFail();

        $validated['facility_staff_uuid'] = $facilityStaff->uuid;
        unset($validated['facility_uuid']);

        $schedule = $this->schedule_service->create($validated);

        return response()->json([
            'message' => __('Schedule created successfully.'),
            // 'data' => new ScheduleResource($schedule->load('facilityStaff.facility')),
        ], 201);
    }

    public function update(UpdateScheduleRequest $request, StaffSchedule $schedule): JsonResponse
    {
        $staff = $request->user()->staff()->firstOrFail();

        $facilityStaff = FacilityStaff::query()
            ->where('staff_id', $staff->id)
            ->where('id', $schedule->facility_staff_id)
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

        $schedule = $this->schedule_service->update($schedule, $validated);

        return response()->json([
            'message' => __('Schedule updated successfully.'),
            'data' => new ScheduleResource($schedule->load('facilityStaff.facility')),
        ]);
    }

    public function destroy(Request $request, StaffSchedule $schedule): JsonResponse
    {
        $staff = $request->user()->staff()->firstOrFail();

        FacilityStaff::query()
            ->where('staff_id', $staff->id)
            ->where('id', $schedule->facility_staff_id)
            ->firstOrFail();

        $this->schedule_service->destroy($schedule);

        return response()->json([
            'message' => __('Schedule deleted successfully.'),
        ]);
    }
}
