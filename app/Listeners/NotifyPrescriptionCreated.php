<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PrescriptionCreated;
use App\Notifications\Prescriptions\PrescriptionCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyPrescriptionCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(PrescriptionCreated $event): void
    {
        $prescription = $event->prescription;
        $locale = app()->getLocale();

        $patient = $prescription->appointment?->patient?->user;
        if ($patient) {
            $patient->notify(
                PrescriptionCreatedNotification::forPatient($prescription, $patient->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('prescription.created', $prescription->appointment?->patient?->user_id ?? 0, 'system');
    }
}
