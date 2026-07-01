<?php

declare(strict_types=1);

namespace App\Notifications\Appointments;

use App\Models\Appointment;
use App\Notifications\BaseNotification;

class AppointmentCreatedNotification extends BaseNotification
{
    public static function forPatient(Appointment $appointment, ?string $locale = null): static
    {
        return new static(
            event: 'appointment.created',
            role: 'patient',
            data: [
                'doctor' => $appointment->facilityStaff?->staff?->user?->name ?? 'Doctor',
                'facility' => $appointment->facilityStaff?->facility?->name ?? 'A facility',
                'start_at' => $appointment->start_at?->format('Y-m-d H:i') ?? '',
                'action_text' => 'View Appointment',
                'action_url' => route('dashboard.appointments.show', $appointment),
            ],
            locale: $locale,
        );
    }

    public static function forDoctor(Appointment $appointment, ?string $locale = null): static
    {
        return new static(
            event: 'appointment.created',
            role: 'doctor',
            data: [
                'patient' => $appointment->patient?->user?->name ?? 'A patient',
                'start_at' => $appointment->start_at?->format('Y-m-d H:i') ?? '',
                'action_text' => 'View Appointment',
                'action_url' => route('dashboard.appointments.show', $appointment),
            ],
            locale: $locale,
        );
    }
}
