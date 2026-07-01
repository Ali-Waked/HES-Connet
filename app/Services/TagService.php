<?php

namespace App\Services;

use App\Events\TagCreated;
use App\Events\TagDeleted;
use App\Events\TagUpdated;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagService
{
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return Tag::query()
            ->withCount('articles')
            ->when(
                $search,
                fn ($query) => $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Tag
    {
        $tag = Tag::create($data);

        event(new TagCreated($tag));

        return $tag;
    }

    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        $tag = $tag->refresh();

        event(new TagUpdated($tag));

        return $tag;
    }

    public function destroy(Tag $tag): void
    {
        $tag->delete();

        event(new TagDeleted($tag));
    }

    public function getStats(): array
    {
        $topTag = Tag::withCount('articles')
            ->orderByDesc('articles_count')
            ->first();

        return [
            'total' => Tag::count(),
            'used' => Tag::has('articles')->count(),
            'unused' => Tag::doesntHave('articles')->count(),
            'top_tag' => $topTag ? [
                'uuid' => $topTag->uuid,
                'name' => $topTag->getTranslations('name'),
                'articles_count' => (int) $topTag->articles_count,
            ] : null,
        ];
    }
}
