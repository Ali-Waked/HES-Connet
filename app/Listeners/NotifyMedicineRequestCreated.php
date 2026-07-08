<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MedicineRequestCreated;
use App\Notifications\Medicines\MedicineRequestCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyMedicineRequestCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(MedicineRequestCreated $event): void
    {
        $medicationRequest = $event->medicationRequest;
        $locale = app()->getLocale();

        $pharmacist = $medicationRequest->pharmacist?->user;
        if ($pharmacist) {
            $pharmacist->notify(
                MedicineRequestCreatedNotification::forPharmacist($medicationRequest, $pharmacist->locale?->value ?? $locale),
            );
        }

        $admins = $this->resolver->admins();
        foreach ($admins as $admin) {
            $admin->notify(
                MedicineRequestCreatedNotification::forAdmin($medicationRequest, $admin->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('medicine.request.created', $medicationRequest->patient?->user_id ?? 0, 'system');
    }
}
