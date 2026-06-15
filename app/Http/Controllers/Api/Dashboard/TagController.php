<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function __construct(
        private readonly TagService $tag_service
    ) {}

    public function index()
    {
        return TagResource::collection(
            $this->tag_service->paginate(
                (int) request('per_page', 15),
                request('search')
            )
        );
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tag_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('Tag created successfully.'),
            'data' => new TagResource($tag),
        ], 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        $tag->loadCount('articles');

        return response()->json([
            'data' => new TagResource($tag),
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $tag = $this->tag_service->update(
            $tag,
            $request->validated()
        );

        return response()->json([
            'message' => __('Tag updated successfully.'),
            'data' => new TagResource($tag),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->tag_service->destroy($tag);

        return response()->json([
            'message' => __('Tag deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->tag_service->getStats()
        );
    }
}
