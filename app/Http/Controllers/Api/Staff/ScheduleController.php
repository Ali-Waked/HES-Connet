<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreScheduleRequest;
use App\Http\Requests\Staff\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\StaffSchedule;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleService $scheduleService,
    ) {}

    public function index(Request $request, Facility $facility): JsonResponse
    {
        $staff = $request->user()->staff()->firstOrFail();

        $facilityStaff = FacilityStaff::query()
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $schedules = StaffSchedule::query()
            ->where('facility_staff_id', $facilityStaff->id)
            ->with('facilityStaff.facility')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'data' => ScheduleResource::collection($schedules),
        ]);
    }

    public function store(StoreScheduleRequest $request, Facility $facility): JsonResponse
    {
        $staff = $request->user()->staff()->firstOrFail();

        $facilityStaff = FacilityStaff::query()
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->active()
            ->firstOrFail();

        $data = $request->validated();
        $data['facility_staff_uuid'] = $facilityStaff->uuid;

        $schedule = $this->scheduleService->create($data);

        return response()->json([
            'message' => __('Schedule created successfully.'),
            'data' => new ScheduleResource($schedule->load('facilityStaff.facility')),
        ], 201);
    }

    public function update(
        UpdateScheduleRequest $request,
        Facility $facility,
        StaffSchedule $schedule,
    ): JsonResponse {
        $staff = $request->user()->staff()->firstOrFail();

        FacilityStaff::query()
            ->where('id', $schedule->facility_staff_id)
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $schedule = $this->scheduleService->update(
            $schedule,
            $request->validated(),
        );

        return response()->json([
            'message' => __('Schedule updated successfully.'),
            'data' => new ScheduleResource($schedule->load('facilityStaff.facility')),
        ]);
    }

    public function destroy(
        Request $request,
        Facility $facility,
        StaffSchedule $schedule,
    ): JsonResponse {
        $staff = $request->user()->staff()->firstOrFail();

        FacilityStaff::query()
            ->where('id', $schedule->facility_staff_id)
            ->where('facility_id', $facility->id)
            ->where('staff_id', $staff->id)
            ->firstOrFail();

        $this->scheduleService->destroy($schedule);

        return response()->json([
            'message' => __('Schedule deleted successfully.'),
        ]);
    }
}
