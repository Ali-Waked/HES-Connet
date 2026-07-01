<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\Users\UserRegisteredNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyUserRegistered
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(UserRegistered $event): void
    {
        $locale = $event->user->locale ?? app()->getLocale();

        // Notify the user themselves
        $event->user->notify(
            UserRegisteredNotification::forOwner($event->user, $locale),
        );

        // Notify admins
        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                UserRegisteredNotification::forAdmin($event->user, $locale),
            );
        }

        $this->logService->markSent('user.registered', $event->user->id, 'system');
    }
}
