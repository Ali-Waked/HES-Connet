<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SymptomDeleted;
use App\Notifications\Symptoms\SymptomDeletedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifySymptomDeleted
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(SymptomDeleted $event): void
    {
        $symptom = $event->symptom;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                SymptomDeletedNotification::forAdmin($symptom, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('symptom.deleted', 0, 'system');
    }
}
