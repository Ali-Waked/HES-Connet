<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Str;

class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     */
    public function creating(Article $article): void
    {
        //  $title = $article->getTranslation('title', 'en');

        $slug = Str::slug($article->title['en'] ?? $article->title ?? '');
        $slug = (($count = Article::where('slug', 'slug')->count()) ? $slug : $slug.'-'.$count);

        $article->slug = $slug;
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "force deleted" event.
     */
    public function forceDeleted(Article $article): void
    {
        //
    }
}
