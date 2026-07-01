<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Symptom\IndexSymptomRequest;
use App\Http\Requests\Api\Dashboard\Symptom\StoreSymptomRequest;
use App\Http\Requests\Api\Dashboard\Symptom\UpdateSymptomRequest;
use App\Http\Resources\SymptomResource;
use App\Models\Symptom;
use App\Services\SymptomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SymptomController extends Controller
{
    public function __construct(
        private readonly SymptomService $symptom_service,
    ) {}

    public function index(IndexSymptomRequest $request): AnonymousResourceCollection
    {
        $symptoms = $this->symptom_service->paginate(
            perPage: (int) $request->integer('per_page', 15),
            search: $request->search,
            status: $request->status,
            sortBy: $request->sort_by ?? 'created_at',
            orderBy: $request->order_by ?? 'desc',
        );

        return SymptomResource::collection($symptoms);
    }

    public function store(StoreSymptomRequest $request): JsonResponse
    {
        $symptom = $this->symptom_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('Symptom created successfully.'),
            'data' => new SymptomResource($symptom),
        ], 201);
    }

    public function show(Symptom $symptom): JsonResponse
    {
        return response()->json([
            'data' => new SymptomResource(
                $this->symptom_service->show($symptom)
            ),
        ]);
    }

    public function update(UpdateSymptomRequest $request, Symptom $symptom): JsonResponse
    {
        $symptom = $this->symptom_service->update(
            $symptom,
            $request->validated()
        );

        return response()->json([
            'message' => __('Symptom updated successfully.'),
            'data' => new SymptomResource($symptom),
        ]);
    }

    public function destroy(Symptom $symptom): JsonResponse
    {
        $this->symptom_service->destroy($symptom);

        return response()->json([
            'message' => __('Symptom deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->symptom_service->getStats()
        );
    }
}
