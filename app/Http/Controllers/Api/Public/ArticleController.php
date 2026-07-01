<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Article\ArticleRequest;
use App\Http\Resources\Public\ArticleCollection;
use App\Http\Resources\Public\ShowArticleResource;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index(ArticleRequest $request): ArticleCollection
    {
        $articles = Article::query()
            ->with(['author', 'category', 'tags'])
            ->withCount('comments')
            ->published()
            ->when(
                $request->search,
                fn ($query, $search) => $query->where(function ($q) use ($search) {
                    $q->where('title->en', 'like', "%{$search}%")
                        ->orWhere('title->ar', 'like', "%{$search}%")
                        ->orWhere('content->en', 'like', "%{$search}%")
                        ->orWhere('content->ar', 'like', "%{$search}%");
                })
            )
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->created_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->created_to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when(
                $request->sort_by === 'oldest',
                fn ($q) => $q->oldest('published_at'),
                fn ($q) => $q->latest('published_at'),
            )
            ->paginate($request->per_page ?? 15);

        return new ArticleCollection($articles);
    }

    public function show(Article $article): ShowArticleResource
    {
        abort_unless($article->status === ArticleStatus::PUBLISHED, 404);

        $article->increment('views');

        $article->load(['author', 'category', 'tags', 'comments.user.profile'])->loadCount('comments');

        return new ShowArticleResource($article);
    }
}
