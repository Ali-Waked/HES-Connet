<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TagUpdated;
use App\Notifications\Tags\TagUpdatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyTagUpdated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(TagUpdated $event): void
    {
        $tag = $event->tag;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                TagUpdatedNotification::forAdmin($tag, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('tag.updated', 0, 'system');
    }
}
