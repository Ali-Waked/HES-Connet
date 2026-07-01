<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Notifications\Users\UserLoggedInNotification;
use App\Services\Notification\NotificationLogService;

class NotifyUserLogin
{
    public function __construct(
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(UserLoggedIn $event): void
    {
        $locale = $event->user->locale ?? app()->getLocale();

        $event->user->notify(
            UserLoggedInNotification::forOwner($event->user, $locale),
        );

        $this->logService->markSent('user.logged_in', $event->user->id, 'system');
    }
}
