<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\SeoAgent;
use App\Ai\Contracts\AiProvider;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        private readonly UuidResolver $uuid_resolver,
        private readonly AiProvider $provider,
    ) {}

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

            if (! empty($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
                $data['cover_image'] = $data['cover_image']->store('articles/cover', 'public');
            }

            unset($data['tags']);

            $article = Article::create($data);

            if (! empty($tagIds)) {
                $article->tags()->sync($tagIds);
            }

            $this->generateSeoMetadata($article);

            return $article->load(['category', 'author', 'tags']);
        });
    }

    public function generateSeoMetadata(Article $article): void
    {
        $content = strip_tags($article->getRawOriginal('content')['en'] ?? '');

        if (empty($content)) {
            return;
        }

        $title = $article->getRawOriginal('title')['en'] ?? $article->title;

        $prompt = "Generate SEO metadata and summary (maximum words: 100) for this blog post.\n"
            ."- Post title: {$title}\n"
            ."- Post Content: {$content}";

        $agent = new SeoAgent;
        $response = $this->provider->chat($agent->instructions(), $prompt);

        $parsed = $this->parseJsonResponse($response);

        $article->meta = [
            'title' => $parsed['title'] ?? $title,
            'description' => $parsed['description'] ?? '',
            'keywords' => is_array($parsed['keywords'] ?? null)
                ? implode(', ', $parsed['keywords'])
                : ($parsed['keywords'] ?? ''),
            'summary' => $parsed['summary'] ?? '',
        ];

        $article->save();

        $this->syncTagsFromKeywords($article, $parsed['keywords'] ?? []);
    }

    private function syncTagsFromKeywords(Article $article, array|string $keywords): void
    {
        $keywordList = is_string($keywords)
            ? array_map('trim', explode(',', $keywords))
            : $keywords;

        $tagIds = [];
        foreach ($keywordList as $keyword) {
            $keyword = trim((string) $keyword);
            if (empty($keyword)) {
                continue;
            }
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($keyword)],
                ['name' => ['en' => $keyword, 'ar' => $keyword]]
            );
            $tagIds[] = $tag->id;
        }

        if (! empty($tagIds)) {
            $existingIds = $article->tags()->pluck('tags.id')->toArray();
            $article->tags()->sync(array_unique([...$existingIds, ...$tagIds]));
        }
    }

    private function parseJsonResponse(string $content): array
    {
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $json = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return [];
    }
}
