<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffPosition\StoreStaffPositionRequest;
use App\Http\Requests\StaffPosition\UpdateStaffPositionRequest;
use App\Http\Resources\StaffPositionResource;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use Illuminate\Http\JsonResponse;

class StaffPositionController extends Controller
{
    public function __construct(
        private readonly StaffPositionService $staff_position_service
    ) {}

    public function index()
    {
        return StaffPositionResource::collection(
            $this->staff_position_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('is_active') !== null ? filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN) : null
            )
        );
    }

    public function store(StoreStaffPositionRequest $request): JsonResponse
    {
        $staffPosition = $this->staff_position_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('Staff position created successfully.'),
            'data' => new StaffPositionResource($staffPosition),
        ], 201);
    }

    public function show(StaffPosition $staffPosition): JsonResponse
    {
        return response()->json([
            'data' => new StaffPositionResource($staffPosition),
        ]);
    }

    public function update(UpdateStaffPositionRequest $request, StaffPosition $staffPosition): JsonResponse
    {
        $staffPosition = $this->staff_position_service->update(
            $staffPosition,
            $request->validated()
        );

        return response()->json([
            'message' => __('Staff position updated successfully.'),
            'data' => new StaffPositionResource($staffPosition),
        ]);
    }

    public function destroy(StaffPosition $staffPosition): JsonResponse
    {
        $this->staff_position_service->destroy($staffPosition);

        return response()->json([
            'message' => __('Staff position deleted successfully.'),
        ]);
    }

    public function lookup(): JsonResponse
    {
        return response()->json(
            StaffPosition::active()
                ->orderBy('name->ar')
                ->get(['uuid', 'name'])
                ->map(fn (StaffPosition $position) => [
                    'uuid' => $position->uuid,
                    'name' => $position->getTranslations('name'),
                ])
        );
    }
}
