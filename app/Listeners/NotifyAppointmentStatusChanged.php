<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AppointmentStatusChanged;
use App\Notifications\Appointments\AppointmentStatusChangedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyAppointmentStatusChanged
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(AppointmentStatusChanged $event): void
    {
        $appointment = $event->appointment;
        $locale = app()->getLocale();

        $patient = $appointment->patient?->user;
        if ($patient) {
            $patient->notify(
                AppointmentStatusChangedNotification::forPatient($appointment, $patient->locale?->value ?? $locale),
            );
        }

        $doctor = $appointment->facilityStaff?->staff?->user;
        if ($doctor) {
            $doctor->notify(
                AppointmentStatusChangedNotification::forDoctor($appointment, $doctor->locale?->value ?? $locale),
            );
        }

        $this->logService->markSent('appointment.status.changed', $appointment->patient?->user_id ?? 0, 'system');
    }
}
