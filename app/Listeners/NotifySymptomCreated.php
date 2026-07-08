<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SymptomCreated;
use App\Notifications\Symptoms\SymptomCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifySymptomCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(SymptomCreated $event): void
    {
        $symptom = $event->symptom;
        $locale = app()->getLocale();

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                SymptomCreatedNotification::forAdmin($symptom, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('symptom.created', 0, 'system');
    }
}
