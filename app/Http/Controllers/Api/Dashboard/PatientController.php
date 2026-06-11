<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    public function __construct(private readonly PatientService $patient_service)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return PatientResource::collection(
            $this->patient_service->paginate(
                (int) request('per_page', 15),
                request('search')
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = $this->patient_service->create($request->validated());

        return response()->json([
            'message' => __('Patient created successfully.'),
            'data' => new PatientResource($patient),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): PatientResource
    {
        return new PatientResource(
            $this->patient_service->show($patient)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        $patient = $this->patient_service->update($patient, $request->validated());

        return response()->json([
            'message' => __('Patient updated successfully.'),
            'data' => new PatientResource($patient),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $this->patient_service->destroy($patient);

        return response()->json([
            'message' => __('Patient deleted successfully.'),
        ]);
    }
}
