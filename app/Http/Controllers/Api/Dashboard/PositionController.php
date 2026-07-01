<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Position\StorePositionRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    public function __construct(
        private readonly PositionService $position_service
    ) {}

    public function index()
    {
        return PositionResource::collection(
            $this->position_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('status') !== null
                    ? filter_var((int) request('status'), FILTER_VALIDATE_BOOLEAN)
                    : null
            )
        );
    }

    public function store(StorePositionRequest $request): Position
    {
        return $this->position_service->create(
            $request->validated()
        );
    }

    public function show(Position $position): JsonResponse
    {
        return response()->json([
            'data' => [
                'uuid' => $position->uuid,
                'name' => $position->getTranslations('name'),
                'description' => $position->getTranslations('description'),
                'is_active' => $position->is_active,
            ],
        ]);
    }

    public function update(
        UpdatePositionRequest $request,
        Position $position
    ): JsonResponse {
        $position = $this->position_service->update(
            $position,
            $request->validated()
        );

        return response()->json([
            'message' => __('Position updated successfully.'),
            'data' => new PositionResource($position),
        ]);
    }

    public function destroy(Position $position): JsonResponse
    {
        $this->position_service->destroy($position);

        return response()->json([
            'message' => __('Position deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->position_service->getStats()
        );
    }

    public function lookup()
    {
        return response()->json(
            Position::query()
                ->where('is_active', true)
                ->orderBy('name->ar')
                ->get(['uuid', 'name'])
        );
    }
}
