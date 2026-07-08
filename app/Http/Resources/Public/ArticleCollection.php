<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Enums\CategoriesType;
use App\Http\Resources\Cards\ArticleCardResource;
use App\Http\Resources\CategoryResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ArticleCollection extends ResourceCollection
{
    public $collects = ArticleResource::class;

    public function with(Request $request): array
    {
        return [
            'most_read' => ArticleCardResource::collection(
                Article::published()
                    ->with('author:id,uuid,name', 'category:id,uuid,name')
                    ->withCount('comments')
                    ->latest('views')
                    ->take(5)
                    ->get()
            ),
            'popular_topics' => Tag::query()
                ->withCount('articles')
                ->whereHas('articles')
                ->orderByDesc('articles_count')
                ->take(10)
                ->get()
                ->map(fn (Tag $tag) => [
                    'uuid' => $tag->uuid,
                    'name' => $tag->getTranslations('name'),
                    'articles_count' => $tag->articles_count,
                ]),
            'categories' => CategoryResource::collection(
                Category::query()
                    ->where('type', CategoriesType::ARTICLE)
                    ->where('is_active', true)
                    ->get()
            ),
        ];
    }
}
