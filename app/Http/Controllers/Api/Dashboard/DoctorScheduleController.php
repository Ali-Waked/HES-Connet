<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorSchedule\StoreDoctorScheduleRequest;
use App\Http\Requests\DoctorSchedule\UpdateDoctorScheduleRequest;
use App\Http\Resources\DoctorScheduleResource;
use App\Models\DoctorSchedule;
use App\Services\DoctorScheduleService;
use Illuminate\Http\JsonResponse;

class DoctorScheduleController extends Controller
{
    public function __construct(private readonly DoctorScheduleService $doctor_schedule_service)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return DoctorScheduleResource::collection(
            $this->doctor_schedule_service->paginate(
                (int) request('per_page', 15),
                request('staff_id')
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorScheduleRequest $request): JsonResponse
    {
        $doctorSchedule = $this->doctor_schedule_service->create($request->validated());

        return response()->json([
            'message' => __('Doctor schedule created successfully.'),
            'data' => new DoctorScheduleResource($doctorSchedule),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorSchedule $doctorSchedule): DoctorScheduleResource
    {
        return new DoctorScheduleResource(
            $this->doctor_schedule_service->show($doctorSchedule)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule): JsonResponse
    {
        $doctorSchedule = $this->doctor_schedule_service->update($doctorSchedule, $request->validated());

        return response()->json([
            'message' => __('Doctor schedule updated successfully.'),
            'data' => new DoctorScheduleResource($doctorSchedule),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorSchedule $doctorSchedule): JsonResponse
    {
        $this->doctor_schedule_service->destroy($doctorSchedule);

        return response()->json([
            'message' => __('Doctor schedule deleted successfully.'),
        ]);
    }
}
