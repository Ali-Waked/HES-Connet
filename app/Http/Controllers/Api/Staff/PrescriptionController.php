<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prescription\StorePrescriptionRequest;
use App\Http\Resources\FacilityPrescriptionResource;
use App\Http\Resources\PrescriptionResource;
use App\Models\Facility;
use App\Models\Prescription;
use App\Services\PrescriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PrescriptionController extends Controller
{
    public function __construct(private readonly PrescriptionService $prescriptionService) {}

    public function index(Request $request, Facility $facility): AnonymousResourceCollection
    {

        $prescriptions = $this->prescriptionService->paginate(
            auth()->user()->staff,
            $facility,
            $request->integer('per_page', 15),
            $request->search
        );

        return FacilityPrescriptionResource::collection($prescriptions);
    }

    public function store(StorePrescriptionRequest $request, Facility $facility): JsonResponse
    {

        $prescription = $this->prescriptionService->create(
            auth()->user()->staff,
            $facility,
            $request->validated()
        );

        return response()->json([
            'message' => __('Prescription created successfully.'),
            'data' => new PrescriptionResource($prescription),
        ], 201);
    }

    public function show(Facility $facility, Prescription $prescription): JsonResponse
    {
        $pres = $this->prescriptionService->show($facility, $prescription);

        return response()->json([
            'data' => new PrescriptionResource($pres),
        ]);
    }
}
