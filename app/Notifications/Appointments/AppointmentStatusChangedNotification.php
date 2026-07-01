<?php

declare(strict_types=1);

namespace App\Notifications\Appointments;

use App\Models\Appointment;
use App\Notifications\BaseNotification;

class AppointmentStatusChangedNotification extends BaseNotification
{
    public static function forPatient(Appointment $appointment, ?string $locale = null): static
    {
        return new static(
            event: 'appointment.status_changed',
            role: 'patient',
            data: [
                'status' => $appointment->status?->value ?? $appointment->status ?? 'changed',
                'reason' => $appointment->cancellation_reason ?? '',
                'doctor' => $appointment->facilityStaff?->staff?->user?->name ?? 'Doctor',
                'action_text' => 'View Appointment',
                'action_url' => route('dashboard.appointments.show', $appointment),
            ],
            locale: $locale,
        );
    }

    public static function forDoctor(Appointment $appointment, ?string $locale = null): static
    {
        return new static(
            event: 'appointment.status_changed',
            role: 'doctor',
            data: [
                'status' => $appointment->status?->value ?? $appointment->status ?? 'changed',
                'reason' => $appointment->cancellation_reason ?? '',
                'patient' => $appointment->patient?->user?->name ?? 'A patient',
                'action_text' => 'View Appointment',
                'action_url' => route('dashboard.appointments.show', $appointment),
            ],
            locale: $locale,
        );
    }
}
