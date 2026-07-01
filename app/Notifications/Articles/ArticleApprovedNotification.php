<?php

declare(strict_types=1);

namespace App\Notifications\Articles;

use App\Models\Article;
use App\Notifications\BaseNotification;

class ArticleApprovedNotification extends BaseNotification
{
    public static function forAuthor(Article $article, ?string $locale = null): static
    {
        return new static(
            event: 'article.approved',
            role: 'author',
            data: [
                'title' => $article->title,
                'action_text' => 'View Article',
                'action_url' => route('articles.show', $article),
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(Article $article, ?string $locale = null): static
    {
        return new static(
            event: 'article.approved',
            role: 'admin',
            data: [
                'title' => $article->title,
                'action_text' => 'View Article',
                'action_url' => route('articles.show', $article),
            ],
            locale: $locale,
        );
    }
}
