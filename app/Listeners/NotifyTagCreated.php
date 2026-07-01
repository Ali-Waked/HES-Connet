<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TagCreated;
use App\Notifications\Tags\TagCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyTagCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(TagCreated $event): void
    {
        $tag = $event->tag;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                TagCreatedNotification::forAdmin($tag, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('tag.created', 0, 'system');
    }
}
