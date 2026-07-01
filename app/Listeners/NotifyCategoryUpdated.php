<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CategoryUpdated;
use App\Notifications\Categories\CategoryUpdatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyCategoryUpdated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(CategoryUpdated $event): void
    {
        $category = $event->category;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                CategoryUpdatedNotification::forAdmin($category, $admin->locale ?? $locale),
            );
        }

        $this->logService->markSent('category.updated', 0, 'system');
    }
}
