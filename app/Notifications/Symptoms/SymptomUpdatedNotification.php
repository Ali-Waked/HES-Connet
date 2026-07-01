<?php

declare(strict_types=1);

namespace App\Notifications\Symptoms;

use App\Models\Symptom;
use App\Notifications\BaseNotification;

class SymptomUpdatedNotification extends BaseNotification
{
    public static function forAdmin(Symptom $symptom, ?string $locale = null): static
    {
        return new static(
            event: 'symptom.updated',
            role: 'admin',
            data: [
                'name' => $symptom->getTranslations('name')['en'] ?? $symptom->name,
            ],
            locale: $locale,
        );
    }
}
