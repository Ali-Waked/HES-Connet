<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ArticleCreated;
use App\Notifications\Articles\ArticleCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyArticleCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(ArticleCreated $event): void
    {
        $article = $event->article;
        $locale = app()->getLocale();

        // Notify author
        $author = $this->resolver->articleAuthor($article);
        if ($author) {
            $author->notify(
                ArticleCreatedNotification::forAuthor($article, $author->locale?->value ?? $locale),
            );
        }

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                ArticleCreatedNotification::forAdmin($article, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('article.created', $article->author?->id ?? 0, 'system');
    }
}
