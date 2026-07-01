<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CategoryDeleted;
use App\Notifications\Categories\CategoryDeletedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyCategoryDeleted
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(CategoryDeleted $event): void
    {
        $category = $event->category;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                CategoryDeletedNotification::forAdmin($category, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('category.deleted', 0, 'system');
    }
}
