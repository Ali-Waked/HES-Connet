<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreArticleRequest;
use App\Http\Requests\Staff\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $article_service
    ) {}

    public function index(Request $request)
    {
        info(Article::where('author_id', auth()->id())->get()->toArray());
        $articles = Article::query()
            ->where('author_id', $request->user()->id)
            ->with(['category', 'author', 'tags'])
            ->when(
                $request->search,
                fn ($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('title->en', 'like', "%{$v}%")
                        ->orWhere('title->ar', 'like', "%{$v}%");
                })
            )
            ->when(
                $request->status,
                fn ($q, $v) => $q->where('status', $v)
            )
            ->when(
                $request->category_id,
                fn ($q, $v) => $q->where('category_id', $v)
            )
            ->when($request->created_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->created_to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when(
                $request->sort_by === 'oldest',
                fn ($q) => $q->oldest(),
                fn ($q) => $q->latest(),
            )
            ->paginate((int) $request->integer('per_page', 15));

        return ArticleResource::collection($articles);
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
        // $this->authorize('view', $article);

        return response()->json([
            'data' => new ArticleResource(
                $this->article_service->show($article)
            ),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        // $this->authorize('update', $article);

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
        // $this->authorize('delete', $article);

        $this->article_service->destroy($article);

        return response()->json([
            'message' => __('Article deleted successfully.'),
        ]);
    }
}
