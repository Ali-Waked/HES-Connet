<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Staff\UpdateSpecializationSymptomsRequest;
use App\Http\Resources\SymptomResource;
use App\Models\Specialization;
use App\Services\SymptomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SymptomController extends Controller
{
    public function __construct(
        private readonly SymptomService $symptom_service,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return SymptomResource::collection(
            $this->symptom_service->getActive(
                search: request('search'),
                perPage: 200,
            )
        );
    }

    public function update(UpdateSpecializationSymptomsRequest $request, Specialization $specialization): JsonResponse
    {
        $specialization = $this->symptom_service->syncSpecializationSymptoms(
            $specialization,
            $request->validated('symptom_ids'),
        );

        return response()->json([
            'message' => __('Symptoms updated successfully.'),
            'data' => [
                'id' => $specialization->id,
                'uuid' => $specialization->uuid,
                'name' => $specialization->getTranslations('name'),
                'symptoms' => SymptomResource::collection($specialization->symptoms),
            ],
        ]);
    }
}
