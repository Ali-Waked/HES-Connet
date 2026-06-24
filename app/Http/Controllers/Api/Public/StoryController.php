<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Services\StoryService;
use Illuminate\Http\JsonResponse;

class StoryController extends Controller
{
    public function __construct(
        private StoryService $storyService
    ) {}

    public function store(StoreStoryRequest $request): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        $story = $this->storyService->create(
            $patient,
            $request->validated()
        );

        return response()->json([
            'message' => 'Story created successfully',
            'data' => $story,
        ], 201);
    }
}
