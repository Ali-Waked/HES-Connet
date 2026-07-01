<?php

declare(strict_types=1);

namespace App\Notifications\Medicines;

use App\Models\MedicationRequest;
use App\Notifications\BaseNotification;

class MedicineRequestCreatedNotification extends BaseNotification
{
    public static function forPharmacist(MedicationRequest $medicationRequest, ?string $locale = null): static
    {
        return new static(
            event: 'medicine.request.created',
            role: 'pharmacist',
            data: [
                'patient' => $medicationRequest->patient?->user?->name ?? 'A patient',
                'facility' => $medicationRequest->facility?->name ?? 'A facility',
                'action_text' => 'View Request',
                'action_url' => route('dashboard.medication-requests.show', $medicationRequest),
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(MedicationRequest $medicationRequest, ?string $locale = null): static
    {
        return new static(
            event: 'medicine.request.created',
            role: 'admin',
            data: [
                'patient' => $medicationRequest->patient?->user?->name ?? 'A patient',
                'facility' => $medicationRequest->facility?->name ?? 'A facility',
                'action_text' => 'View Request',
                'action_url' => route('dashboard.medication-requests.show', $medicationRequest),
            ],
            locale: $locale,
        );
    }
}
