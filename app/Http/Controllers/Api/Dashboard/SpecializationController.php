<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Specialization\AttachSpecializationSymptomsRequest;
use App\Http\Requests\Api\Dashboard\Specialization\IndexSpecializationRequest;
use App\Http\Requests\Api\Dashboard\Specialization\StoreSpecializationRequest;
use App\Http\Requests\Api\Dashboard\Specialization\SyncSpecializationSymptomsRequest;
use App\Http\Requests\Api\Dashboard\Specialization\UpdateSpecializationRequest;
use App\Http\Resources\LookupResource;
use App\Http\Resources\SpecializationResource;
use App\Http\Resources\SymptomResource;
use App\Models\Specialization;
use App\Models\Symptom;
use App\Services\SpecializationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SpecializationController extends Controller
{
    public function __construct(
        private readonly SpecializationService $specialization_service,
    ) {}

    public function lookup(): AnonymousResourceCollection
    {
        return LookupResource::collection(
            $this->specialization_service->lookup()
        );
    }

    public function index(IndexSpecializationRequest $request): AnonymousResourceCollection
    {
        $specializations = $this->specialization_service->paginate(
            perPage: (int) $request->integer('per_page', 15),
            search: $request->search,
            sortBy: $request->sort_by ?? 'created_at',
            orderBy: $request->order_by ?? 'desc',
        );

        return SpecializationResource::collection($specializations);
    }

    public function store(StoreSpecializationRequest $request): JsonResponse
    {
        $specialization = $this->specialization_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('Specialization created successfully.'),
            'data' => new SpecializationResource($specialization),
        ], 201);
    }

    public function show(Specialization $specialization): JsonResponse
    {
        return response()->json([
            'data' => new SpecializationResource(
                $this->specialization_service->show($specialization)
            ),
        ]);
    }

    public function update(UpdateSpecializationRequest $request, Specialization $specialization): JsonResponse
    {
        $specialization = $this->specialization_service->update(
            $specialization,
            $request->validated()
        );

        return response()->json([
            'message' => __('Specialization updated successfully.'),
            'data' => new SpecializationResource($specialization),
        ]);
    }

    public function destroy(Specialization $specialization): JsonResponse
    {
        $this->specialization_service->destroy($specialization);

        return response()->json([
            'message' => __('Specialization deleted successfully.'),
        ]);
    }

    public function listSymptoms(Specialization $specialization): AnonymousResourceCollection
    {
        return SymptomResource::collection(
            $this->specialization_service->listSymptoms($specialization)
        );
    }

    public function syncSymptoms(SyncSpecializationSymptomsRequest $request, Specialization $specialization): JsonResponse
    {
        $specialization = $this->specialization_service->syncSymptoms(
            $specialization,
            $request->validated('symptom_ids'),
        );

        return response()->json([
            'message' => __('Symptoms synchronized successfully.'),
            'data' => new SpecializationResource($specialization),
        ]);
    }

    public function attachSymptoms(AttachSpecializationSymptomsRequest $request, Specialization $specialization): JsonResponse
    {
        $specialization = $this->specialization_service->attachSymptoms(
            $specialization,
            $request->validated('symptom_ids'),
        );

        return response()->json([
            'message' => __('Symptoms attached successfully.'),
            'data' => new SpecializationResource($specialization),
        ]);
    }

    public function detachSymptom(Specialization $specialization, Symptom $symptom): JsonResponse
    {
        $specialization = $this->specialization_service->detachSymptom($specialization, $symptom);

        return response()->json([
            'message' => __('Symptom detached successfully.'),
            'data' => new SpecializationResource($specialization),
        ]);
    }
}
