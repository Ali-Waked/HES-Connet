<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorUnavailable\StoreDoctorUnavailableRequest;
use App\Http\Requests\DoctorUnavailable\UpdateDoctorUnavailableRequest;
use App\Http\Resources\DoctorUnavailableResource;
use App\Models\DoctorUnavailable;
use App\Services\DoctorUnavailableService;
use Illuminate\Http\JsonResponse;

class DoctorUnavailableController extends Controller
{
    public function __construct(private readonly DoctorUnavailableService $doctor_unavailable_service)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return DoctorUnavailableResource::collection(
            $this->doctor_unavailable_service->paginate(
                (int) request('per_page', 15),
                request('staff_id')
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorUnavailableRequest $request): JsonResponse
    {
        $doctorUnavailable = $this->doctor_unavailable_service->create($request->validated());

        return response()->json([
            'message' => __('Doctor unavailable created successfully.'),
            'data' => new DoctorUnavailableResource($doctorUnavailable),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorUnavailable $doctorUnavailable): DoctorUnavailableResource
    {
        return new DoctorUnavailableResource(
            $this->doctor_unavailable_service->show($doctorUnavailable)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorUnavailableRequest $request, DoctorUnavailable $doctorUnavailable): JsonResponse
    {
        $doctorUnavailable = $this->doctor_unavailable_service->update($doctorUnavailable, $request->validated());

        return response()->json([
            'message' => __('Doctor unavailable updated successfully.'),
            'data' => new DoctorUnavailableResource($doctorUnavailable),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorUnavailable $doctorUnavailable): JsonResponse
    {
        $this->doctor_unavailable_service->destroy($doctorUnavailable);

        return response()->json([
            'message' => __('Doctor unavailable deleted successfully.'),
        ]);
    }
}
