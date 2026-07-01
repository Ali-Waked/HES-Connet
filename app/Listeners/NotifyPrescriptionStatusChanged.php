<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PrescriptionStatusChanged;
use App\Notifications\Prescriptions\PrescriptionCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyPrescriptionStatusChanged
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(PrescriptionStatusChanged $event): void
    {
        $prescription = $event->prescription;
        $locale = app()->getLocale();

        $patient = $prescription->appointment?->patient?->user;
        if ($patient) {
            $patient->notify(
                PrescriptionCreatedNotification::forPatient($prescription, $patient->locale ?? $locale),
            );
        }

        $this->logService->markSent('prescription.status_changed', $prescription->appointment?->patient?->user_id ?? 0, 'system');
    }
}
