<?php

declare(strict_types=1);

namespace App\Notifications\Articles;

use App\Models\Article;
use App\Notifications\BaseNotification;

class ArticleRejectedNotification extends BaseNotification
{
    public static function forAuthor(Article $article, ?string $locale = null): static
    {
        return new static(
            event: 'article.rejected',
            role: 'author',
            data: [
                'title' => $article->title,
                'reason' => $article->rejection_reason ?? '',
                'action_text' => 'Edit Article',
                'action_url' => route('articles.edit', $article),
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(Article $article, ?string $locale = null): static
    {
        return new static(
            event: 'article.rejected',
            role: 'admin',
            data: [
                'title' => $article->title,
                'reason' => $article->rejection_reason ?? '',
                'action_text' => 'Edit Article',
                'action_url' => route('articles.edit', $article),
            ],
            locale: $locale,
        );
    }
}
