<?php

declare(strict_types=1);

namespace App\Notifications\Medicines;

use App\Models\MedicationRequest;
use App\Notifications\BaseNotification;

class MedicineRequestStatusChangedNotification extends BaseNotification
{
    public static function forPatient(MedicationRequest $medicationRequest, ?string $locale = null): static
    {
        return new static(
            event: 'medicine.request.status_changed',
            role: 'patient',
            data: [
                'status' => $medicationRequest->status?->value ?? $medicationRequest->status ?? 'updated',
                'facility' => $medicationRequest->facility?->name ?? 'A facility',
                'action_text' => 'View Request',
                'action_url' => route('dashboard.medication-requests.show', $medicationRequest),
            ],
            locale: $locale,
        );
    }

    public static function forPharmacist(MedicationRequest $medicationRequest, ?string $locale = null): static
    {
        return new static(
            event: 'medicine.request.status_changed',
            role: 'pharmacist',
            data: [
                'status' => $medicationRequest->status?->value ?? $medicationRequest->status ?? 'updated',
                'patient' => $medicationRequest->patient?->user?->name ?? 'A patient',
                'action_text' => 'View Request',
                'action_url' => route('dashboard.medication-requests.show', $medicationRequest),
            ],
            locale: $locale,
        );
    }
}
