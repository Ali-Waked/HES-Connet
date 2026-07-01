<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchHistory\FilterSearchHistoryRequest;
use App\Http\Requests\Api\SearchHistory\StoreSearchHistoryRequest;
use App\Http\Resources\SearchHistoryResource;
use App\Services\SearchHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchHistoryController extends Controller
{
    public function __construct(
        private readonly SearchHistoryService $searchHistoryService,
    ) {}

    public function index(FilterSearchHistoryRequest $request): AnonymousResourceCollection
    {
        $histories = $this->searchHistoryService->getUserHistory(
            $request->user(),
            type: $request->type,
            perPage: (int) $request->integer('per_page', 20),
        );

        return SearchHistoryResource::collection($histories);
    }

    public function store(StoreSearchHistoryRequest $request): JsonResponse
    {
        $history = $this->searchHistoryService->logSearch(
            $request->user(),
            $request->validated('query'),
            $request->validated('type'),
            $request->validated('filters'),
        );

        return response()->json([
            'data' => new SearchHistoryResource($history),
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->searchHistoryService->clearUserHistory(
            $request->user(),
            $request->type,
        );

        return response()->json(['message' => __('Search history cleared.')]);
    }

    public function trending(): JsonResource
    {
        $trending = $this->searchHistoryService->getTrendingSearches(
            (int) request('limit', 10),
        );

        return JsonResource::collection($trending);
    }

    public function adminIndex(FilterSearchHistoryRequest $request): AnonymousResourceCollection
    {
        $histories = $this->searchHistoryService->adminPaginate(
            type: $request->type,
            search: $request->search,
            perPage: (int) $request->integer('per_page', 20),
        );

        return SearchHistoryResource::collection($histories);
    }
}
