<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Http\Requests\UpdateStoryRequest;
use App\Http\Resources\StoryResource;
use App\Models\Story;
use App\Services\StoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(
        private StoryService $storyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        abort_unless($patient, 403, __('Patient profile not found.'));

        $stories = $patient->stories()
            ->with('category')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->has('is_fundraising'), fn ($q) => $q->where('is_fundraising', $request->boolean('is_fundraising')))
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->latest()
            ->paginate();

        return response()->json(StoryResource::collection($stories));
    }

    public function store(StoreStoryRequest $request): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        $story = $this->storyService->create(
            $patient,
            $request->validated()
        );

        return response()->json([
            'message' => 'Story created successfully',
            'data' => new StoryResource($story),
        ], 201);
    }

    public function update(UpdateStoryRequest $request, Story $story): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        if ($story->patient_id !== $patient->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $story = $this->storyService->update(
            $story,
            $request->validated()
        );

        return response()->json([
            'message' => 'Story updated successfully',
            'data' => new StoryResource($story),
        ]);
    }
}
