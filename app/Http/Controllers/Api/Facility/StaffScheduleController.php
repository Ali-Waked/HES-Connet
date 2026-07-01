<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\StaffSchedule\StoreStaffScheduleRequest;
use App\Http\Requests\Facility\StaffSchedule\UpdateStaffScheduleRequest;
use App\Http\Resources\StaffScheduleResource;
use App\Models\Facility;
use App\Models\StaffSchedule;
use App\Services\FacilityStaffScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffScheduleController extends Controller
{
    public function __construct(
        private readonly FacilityStaffScheduleService $service,
    ) {}

    public function index(Facility $facility): AnonymousResourceCollection
    {
        return StaffScheduleResource::collection(
            $this->service->index($facility)
        );
    }

    public function store(
        StoreStaffScheduleRequest $request,
        Facility $facility,
    ): JsonResponse {
        $schedule = $this->service->store(
            $facility,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Staff schedule created successfully.',
            'data' => new StaffScheduleResource($schedule),
        ], 201);
    }

    public function show(
        Facility $facility,
        StaffSchedule $staffSchedule,
    ): StaffScheduleResource {
        return new StaffScheduleResource(
            $this->service->show($facility, $staffSchedule)
        );
    }

    public function update(
        UpdateStaffScheduleRequest $request,
        Facility $facility,
        StaffSchedule $staffSchedule,
    ): StaffScheduleResource {
        return new StaffScheduleResource(
            $this->service->update(
                $facility,
                $staffSchedule,
                $request->validated(),
            )
        );
    }

    public function destroy(
        Facility $facility,
        StaffSchedule $staffSchedule,
    ): JsonResponse {
        $this->service->destroy($facility, $staffSchedule);

        return response()->json([], 204);
    }
}
