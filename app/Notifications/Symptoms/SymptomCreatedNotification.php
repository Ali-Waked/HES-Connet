<?php

declare(strict_types=1);

namespace App\Notifications\Symptoms;

use App\Models\Symptom;
use App\Notifications\BaseNotification;

class SymptomCreatedNotification extends BaseNotification
{
    public static function forAdmin(Symptom $symptom, ?string $locale = null): static
    {
        return new static(
            event: 'symptom.created',
            role: 'admin',
            data: [
                'name' => $symptom->getTranslations('name')['en'] ?? $symptom->name,
                'action_text' => 'View Symptom',
                'action_url' => route('dashboard.symptoms.show', $symptom),
            ],
            locale: $locale,
        );
    }
}
