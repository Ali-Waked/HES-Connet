<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MedicineRequestStatusChanged;
use App\Notifications\Medicines\MedicineRequestStatusChangedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyMedicineRequestStatusChanged
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(MedicineRequestStatusChanged $event): void
    {
        $medicationRequest = $event->medicationRequest;
        $locale = app()->getLocale();

        $patient = $medicationRequest->patient?->user;
        if ($patient) {
            $patient->notify(
                MedicineRequestStatusChangedNotification::forPatient($medicationRequest, $patient->locale?->value ?? $locale),
            );
        }

        $pharmacist = $medicationRequest->pharmacist?->user;
        if ($pharmacist) {
            $pharmacist->notify(
                MedicineRequestStatusChangedNotification::forPharmacist($medicationRequest, $pharmacist->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('medicine.request.status.changed', $medicationRequest->patient?->user_id ?? 0, 'system');
    }
}
