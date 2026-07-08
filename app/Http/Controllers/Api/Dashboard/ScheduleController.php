<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\StaffSchedule;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleService $schedule_service) {}

    public function index(Request $request): JsonResponse
    {
        $schedules = $this->schedule_service->paginate(
            (int) $request->integer('per_page', 15),
            $request->input('facility_uuid'),
        );

        return response()->json(
            ScheduleResource::collection($schedules)
        );
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['days_of_week'] = [$data['day_of_week']];
        unset($data['day_of_week']);

        $schedule = $this->schedule_service->create($data);

        return response()->json([
            'message' => __('Schedule created successfully.'),
            'data' => new ScheduleResource($schedule),
        ], 201);
    }

    public function show(StaffSchedule $staffSchedule): JsonResponse
    {
        return response()->json([
            'data' => new ScheduleResource(
                $this->schedule_service->show($staffSchedule)
            ),
        ]);
    }

    public function update(UpdateScheduleRequest $request, StaffSchedule $staffSchedule): JsonResponse
    {
        $schedule = $this->schedule_service->update($staffSchedule, $request->validated());

        return response()->json([
            'message' => __('Schedule updated successfully.'),
            'data' => new ScheduleResource($schedule),
        ]);
    }

    public function destroy(StaffSchedule $staffSchedule): JsonResponse
    {
        $this->schedule_service->destroy($staffSchedule);

        return response()->json([
            'message' => __('Schedule deleted successfully.'),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $request->validate([
            'facility_uuid' => ['required', 'exists:facilities,uuid'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $data = $this->schedule_service->calendar(
            $request->input('facility_uuid'),
            (int) $request->input('month'),
            (int) $request->input('year'),
        );

        return response()->json(['data' => $data]);
    }
}
