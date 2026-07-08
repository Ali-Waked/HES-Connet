<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CategoryCreated;
use App\Notifications\Categories\CategoryCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyCategoryCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(CategoryCreated $event): void
    {
        $category = $event->category;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                CategoryCreatedNotification::forAdmin($category, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('category.created', 0, 'system');
    }
}
