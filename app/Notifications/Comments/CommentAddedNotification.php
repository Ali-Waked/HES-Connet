<?php

declare(strict_types=1);

namespace App\Notifications\Comments;

use App\Models\Comment;
use App\Notifications\BaseNotification;

class CommentAddedNotification extends BaseNotification
{
    public static function forOwner(Comment $comment, ?string $locale = null): static
    {
        return new static(
            event: 'comment.added',
            role: 'owner',
            data: [
                'author' => $comment->user?->name ?? 'Someone',
                'article' => $comment->article?->title ?? 'an article',
                'action_text' => 'View Comment',
                'action_url' => route('articles.show', $comment->article),
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(Comment $comment, ?string $locale = null): static
    {
        return new static(
            event: 'comment.added',
            role: 'admin',
            data: [
                'author' => $comment->user?->name ?? 'Someone',
                'article' => $comment->article?->title ?? 'an article',
                'action_text' => 'View Comment',
                'action_url' => route('articles.show', $comment->article),
            ],
            locale: $locale,
        );
    }
}
