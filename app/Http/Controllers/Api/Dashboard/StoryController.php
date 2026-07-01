<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStoryStatusRequest;
use App\Http\Resources\DashboardStoryResource;
use App\Models\Story;
use App\Services\StoryManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(
        private readonly StoryManagementService $storyManagementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        info($request->all());
        $stories = $this->storyManagementService->getStories(
            $request->only([
                'status', 'is_fundraising', 'patient_id', 'category_id', 'search',
                'date_from', 'date_to', 'sort', 'per_page',
            ])
        );

        return DashboardStoryResource::collection($stories)->response();
    }

    public function trash(Request $request): JsonResponse
    {
        // $this->authorize('viewTrash', Story::class);

        $stories = $this->storyManagementService->getTrash(
            $request->only([
                'search', 'date_from', 'date_to', 'patient_id', 'per_page',
            ])
        );

        return DashboardStoryResource::collection($stories)->response();
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->storyManagementService->getStats()
        );
    }

    public function show(Story $story): JsonResponse
    {
        $story = $this->storyManagementService->show($story);

        return response()->json([
            'data' => new DashboardStoryResource($story),
        ]);
    }

    public function updateStatus(UpdateStoryStatusRequest $request, Story $story): JsonResponse
    {
        $story = $this->storyManagementService->updateStatus(
            $story,
            $request->validated()['status']
        );

        return response()->json([
            'message' => 'Story status updated successfully',
            'data' => new DashboardStoryResource($story),
        ]);
    }

    public function destroy(Story $story): JsonResponse
    {
        // $this->authorize('delete', Story::class);

        $this->storyManagementService->delete($story);

        return response()->json([
            'message' => 'Story deleted successfully',
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        // $this->authorize('restore', Story::class);

        $story = $this->storyManagementService->restore($id);

        return response()->json([
            'message' => 'Story restored successfully',
            'data' => new DashboardStoryResource($story),
        ]);
    }

    public function forceDelete(string $id): JsonResponse
    {
        // $this->authorize('forceDelete', Story::class);

        $this->storyManagementService->forceDelete($id);

        return response()->json([
            'message' => 'Story permanently deleted successfully',
        ]);
    }
}
