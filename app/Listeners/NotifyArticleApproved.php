<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ArticleApproved;
use App\Notifications\Articles\ArticleApprovedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyArticleApproved
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(ArticleApproved $event): void
    {
        $article = $event->article;
        $locale = app()->getLocale();

        // Notify author
        $author = $this->resolver->articleAuthor($article);
        if ($author) {
            $author->notify(
                ArticleApprovedNotification::forAuthor($article, $author->locale?->value ?? $locale),
            );
        }

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                ArticleApprovedNotification::forAdmin($article, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('article.approved', $article->author?->id ?? 0, 'system');
    }
}
