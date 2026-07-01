<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\ArticleCreated;
use App\Models\Article;
use App\Models\Category;
use App\Models\Staff;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticleService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        ?string $categoryId = null,
        ?string $authorId = null,
        ?string $createdFrom = null,
        ?string $createdTo = null,
        ?string $sortBy = 'latest',
    ): LengthAwarePaginator {
        return Article::query()
            ->with([
                'category',
                'author',
                'tags',
            ])
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('title->en', 'like', "%{$search}%")
                        ->orWhere('title->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $categoryId,
                fn ($query) => $query->where(
                    'category_id',
                    $this->uuid_resolver->resolve(Category::class, $categoryId)
                )
            )
            ->when(
                $authorId,
                fn ($query) => $query->where(
                    'author_id',
                    $this->uuid_resolver->resolve(Staff::class, $authorId)
                )
            )
            ->when($createdFrom, fn ($q) => $q->whereDate('created_at', '>=', $createdFrom))
            ->when($createdTo, fn ($q) => $q->whereDate('created_at', '<=', $createdTo))
            ->when($sortBy === 'oldest', fn ($q) => $q->oldest())
            ->when($sortBy === 'most_viewed', fn ($q) => $q->orderBy('views', 'desc'), fn ($q) => $q->latest())
            ->paginate($perPage);
    }

    public function create(array $data): Article
    {
        return DB::transaction(function () use ($data) {
            $tagIds = [];

            if (! empty($data['tags'])) {
                $tagIds = array_map(
                    fn (string $uuid) => $this->uuid_resolver->resolve(Tag::class, $uuid),
                    $data['tags']
                );
            }

            $data['category_id'] = $this->uuid_resolver->resolve(
                Category::class,
                $data['category_id']
            );

            $data['author_id'] = auth()->id();

            if (! empty($data['cover_image'])) {
                $data['cover_image'] = $data['cover_image']->store(
                    'articles/cover',
                    'public'
                );
            }

            unset(
                $data['tags'],
            );

            $article = Article::create($data);

            if (! empty($tagIds)) {
                $article->tags()->sync($tagIds);
            }

            event(new ArticleCreated($article));

            return $article->load([
                'category',
                'author',
                'tags',
            ]);
        });
    }

    public function show(Article $article): Article
    {
        return $article->load([
            'category',
            'author',
            'tags',
            'comments.user.profile',
        ])->loadCount('comments');
    }

    public function update(Article $article, array $data): Article
    {
        return DB::transaction(function () use ($article, $data) {
            unset($data['_method']);

            if (! empty($data['category_id'])) {
                $data['category_id'] = $this->uuid_resolver->resolve(
                    Category::class,
                    $data['category_id']
                );
            }

            if (! empty($data['cover_image'])) {
                if ($article->cover_image) {
                    Storage::disk('public')->delete(
                        $article->getRawOriginal('cover_image')
                    );
                }

                $data['cover_image'] = $data['cover_image']->store(
                    'articles/cover',
                    'public'
                );
            }

            $tagIds = null;

            if (! empty($data['tags'])) {
                $tagIds = array_map(
                    fn (string $uuid) => $this->uuid_resolver->resolve(
                        Tag::class,
                        $uuid
                    ),
                    $data['tags']
                );
            }

            unset(
                $data['tags'],
            );

            $article->update($data);

            if ($tagIds !== null) {
                $article->tags()->sync($tagIds);
            }

            return $article->fresh()->load([
                'category',
                'author',
                'tags',
            ]);
        });
    }

    public function destroy(Article $article): void
    {
        DB::transaction(function () use ($article) {
            $originalCover = $article->getRawOriginal('cover_image');
            if ($originalCover) {
                Storage::disk('public')->delete($originalCover);
            }

            $article->tags()->detach();

            $article->delete();
        });
    }

    public function getStats(): array
    {
        $stats = Article::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending_review') as pending_review,
                SUM(status = 'published') as published,
                SUM(status NOT IN ('pending_review', 'published')) as other
            ")
            ->first();

        return [
            'total' => (int) $stats->total,
            'pending' => (int) $stats->pending_review,
            'published' => (int) $stats->published,
            'other' => (int) $stats->other,
        ];
    }
}
