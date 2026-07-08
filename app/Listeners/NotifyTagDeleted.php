<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TagDeleted;
use App\Notifications\Tags\TagDeletedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyTagDeleted
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(TagDeleted $event): void
    {
        $tag = $event->tag;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                TagDeletedNotification::forAdmin($tag, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('tag.deleted', 0, 'system');
    }
}
