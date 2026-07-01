<?php

declare(strict_types=1);

namespace App\Notifications\Prescriptions;

use App\Models\Prescription;
use App\Notifications\BaseNotification;

class PrescriptionCreatedNotification extends BaseNotification
{
    public static function forPatient(Prescription $prescription, ?string $locale = null): static
    {
        return new static(
            event: 'prescription.created',
            role: 'patient',
            data: [
                'doctor' => $prescription->appointment?->facilityStaff?->staff?->user?->name ?? 'Doctor',
                'action_text' => 'View Prescription',
                'action_url' => route('dashboard.prescriptions.show', $prescription),
            ],
            locale: $locale,
        );
    }
}
