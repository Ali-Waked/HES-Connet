<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ArticleRejected;
use App\Notifications\Articles\ArticleRejectedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyArticleRejected
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(ArticleRejected $event): void
    {
        $article = $event->article;
        $locale = app()->getLocale();

        // Notify author
        $author = $this->resolver->articleAuthor($article);
        if ($author) {
            $author->notify(
                ArticleRejectedNotification::forAuthor($article, $author->locale?->value ?? $locale),
            );
        }

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                ArticleRejectedNotification::forAdmin($article, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('article.rejected', $article->author?->id ?? 0, 'system');
    }
}
