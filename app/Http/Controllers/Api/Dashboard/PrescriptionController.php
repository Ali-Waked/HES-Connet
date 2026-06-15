<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prescription\StorePrescriptionRequest;
use App\Http\Requests\Prescription\UpdatePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Services\PrescriptionService;
use Illuminate\Http\JsonResponse;

class PrescriptionController extends Controller
{
    public function __construct(private readonly PrescriptionService $prescription_service)
    {
    }

    public function index()
    {
        return PrescriptionResource::collection(
            $this->prescription_service->paginate(
                (int) request('per_page', 15),
                request('appointment_id'),
            )
        );
    }

    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        $prescription = $this->prescription_service->create($request->validated());

        return response()->json([
            'message' => __('Prescription created successfully.'),
            'data' => new PrescriptionResource($prescription),
        ], 201);
    }

    public function show(Prescription $prescription): JsonResponse
    {
        return response()->json([
            'data' => new PrescriptionResource(
                $this->prescription_service->show($prescription)
            ),
        ]);
    }

    public function update(UpdatePrescriptionRequest $request, Prescription $prescription): JsonResponse
    {
        $prescription = $this->prescription_service->update($prescription, $request->validated());

        return response()->json([
            'message' => __('Prescription updated successfully.'),
            'data' => new PrescriptionResource($prescription),
        ]);
    }

    public function destroy(Prescription $prescription): JsonResponse
    {
        $this->prescription_service->destroy($prescription);

        return response()->json([
            'message' => __('Prescription deleted successfully.'),
        ]);
    }
}
