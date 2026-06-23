<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffSchedule\StoreStaffScheduleRequest;
use App\Http\Requests\StaffSchedule\UpdateStaffScheduleRequest;
use App\Http\Resources\StaffScheduleResource;
use App\Models\StaffSchedule;
use App\Services\StaffScheduleService;
use Illuminate\Http\JsonResponse;

class StaffScheduleController extends Controller
{
    public function __construct(private readonly StaffScheduleService $staff_schedule_service)
    {
    }

    public function index()
    {
        return StaffScheduleResource::collection(
            $this->staff_schedule_service->paginate(
                (int) request('per_page', 15),
                request('facility_staff_id')
            )
        );
    }

    public function store(StoreStaffScheduleRequest $request): JsonResponse
    {
        $staffSchedule = $this->staff_schedule_service->create($request->validated());

        return response()->json([
            'message' => __('Staff schedule created successfully.'),
            'data' => new StaffScheduleResource($staffSchedule),
        ], 201);
    }

    public function show(StaffSchedule $staffSchedule): StaffScheduleResource
    {
        return new StaffScheduleResource(
            $this->staff_schedule_service->show($staffSchedule)
        );
    }

    public function update(UpdateStaffScheduleRequest $request, StaffSchedule $staffSchedule): JsonResponse
    {
        $staffSchedule = $this->staff_schedule_service->update($staffSchedule, $request->validated());

        return response()->json([
            'message' => __('Staff schedule updated successfully.'),
            'data' => new StaffScheduleResource($staffSchedule),
        ]);
    }

    public function destroy(StaffSchedule $staffSchedule): JsonResponse
    {
        $this->staff_schedule_service->destroy($staffSchedule);

        return response()->json([
            'message' => __('Staff schedule deleted successfully.'),
        ]);
    }
}
