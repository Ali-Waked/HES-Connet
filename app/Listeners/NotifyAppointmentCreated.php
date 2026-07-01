<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\Notifications\Appointments\AppointmentCreatedNotification;
use App\Services\Notification\NotificationLogService;
use App\Services\Notification\NotificationRecipientResolver;

class NotifyAppointmentCreated
{
    public function __construct(
        private readonly NotificationRecipientResolver $resolver,
        private readonly NotificationLogService $logService,
    ) {}

    public function handle(AppointmentCreated $event): void
    {
        $appointment = $event->appointment;
        $locale = app()->getLocale();

        $patient = $appointment->patient?->user;
        if ($patient) {
            $patient->notify(
                AppointmentCreatedNotification::forPatient($appointment, $patient->locale ?? $locale),
            );
        }

        $doctor = $appointment->facilityStaff?->staff?->user;
        if ($doctor) {
            $doctor->notify(
                AppointmentCreatedNotification::forDoctor($appointment, $doctor->locale ?? $locale),
            );
        }

        $this->logService->markSent('appointment.created', $appointment->patient?->user_id ?? 0, 'system');
    }
}
