<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleService
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $categoryId = null, ?int $authId = null): LengthAwarePaginator
    {
        return Article::query()
            ->with([
                'category',
                'auth',
            ])
            ->when(
                $search,
                fn ($query) => $query->where(
                    'title',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                $categoryId,
                fn ($query) => $query->where('category_id', $categoryId)
            )
            ->when(
                $authId,
                fn ($query) => $query->where('auth_id', $authId)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Article
    {
        return Article::create($data);
    }

    public function show(Article $article): Article
    {
        return $article->load([
            'category',
            'auth',
        ]);
    }

    public function update(Article $article, array $data): Article
    {
        $article->update($data);

        return $article->refresh();
    }

    public function destroy(Article $article): void
    {
        $article->delete();
    }
}
