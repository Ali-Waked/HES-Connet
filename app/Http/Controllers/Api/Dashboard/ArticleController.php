<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $article_service
    ) {}

    public function index()
    {
        return ArticleResource::collection(
            $this->article_service->paginate(
                perPage: (int) request('per_page', 15),
                search: request('search'),
                status: request('status'),
                categoryId: request('category_id'),
                authorId: request('author_id'),
                createdFrom: request('created_from'),
                createdTo: request('created_to'),
                sortBy: request('sort_by', 'latest'),
            )
        );
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $article = $this->article_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('Article created successfully.'),
            'data' => new ArticleResource($article),
        ], 201);
    }

    public function show(Article $article): JsonResponse
    {
        $article = $this->article_service->show($article);

        return response()->json([
            'data' => new ArticleResource($article),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $article = $this->article_service->update(
            $article,
            $request->validated()
        );

        return response()->json([
            'message' => __('Article updated successfully.'),
            'data' => new ArticleResource($article),
        ]);
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->article_service->destroy($article);

        return response()->json([
            'message' => __('Article deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->article_service->getStats()
        );
    }
}
