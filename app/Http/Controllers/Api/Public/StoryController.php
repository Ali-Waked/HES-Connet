<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\StoryStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoryResource;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $stories = Story::query()
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->with(['patient.user', 'category'])
            ->when(
                $request->search,
                fn ($q) => $q->where(function ($q) use ($request) {
                    $q->where('title->ar', 'like', "%{$request->search}%")
                        ->orWhere('title->en', 'like', "%{$request->search}%")
                        ->orWhere('content->ar', 'like', "%{$request->search}%")
                        ->orWhere('content->en', 'like', "%{$request->search}%");
                })
            )
            ->when(
                $request->has('is_fundraising'),
                fn ($q) => $q->where('is_fundraising', $request->boolean('is_fundraising'))
            )
            ->when(
                $request->category_id,
                fn ($q, $v) => $q->where('category_id', $v)
            )
            ->latest()
            ->paginate((int) ($request->per_page ?? 10));

        return StoryResource::collection($stories)->response();
    }

    public function show(Story $story): JsonResponse
    {
        abort_if(
            $story->status !== StoryStatus::APPROVED,
            404
        );

        $story->loadMissing([
            'patient.user',
            'category',
        ]);

        return (new StoryResource($story))
            ->response();
    }
}
