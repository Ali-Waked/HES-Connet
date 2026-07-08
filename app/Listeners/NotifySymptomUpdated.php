<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SymptomUpdated;
use App\Notifications\Symptoms\SymptomUpdatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifySymptomUpdated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(SymptomUpdated $event): void
    {
        $symptom = $event->symptom;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                SymptomUpdatedNotification::forAdmin($symptom, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('symptom.updated', 0, 'system');
    }
}
