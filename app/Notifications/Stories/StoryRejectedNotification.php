<?php

declare(strict_types=1);

namespace App\Notifications\Stories;

use App\Models\Story;
use App\Notifications\BaseNotification;

class StoryRejectedNotification extends BaseNotification
{
    public static function forPatient(Story $story, ?string $locale = null): static
    {
        return new static(
            event: 'story.rejected',
            role: 'patient',
            data: [
                'title' => $story->getTranslations('title')['en'] ?? $story->title,
                'reason' => $story->rejection_reason ?? '',
                'action_text' => 'Edit Story',
                'action_url' => route('stories.show', $story),
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(Story $story, ?string $locale = null): static
    {
        return new static(
            event: 'story.rejected',
            role: 'admin',
            data: [
                'title' => $story->getTranslations('title')['en'] ?? $story->title,
                'patient' => $story->patient?->user?->name ?? 'A patient',
                'reason' => $story->rejection_reason ?? '',
                'action_text' => 'View Story',
                'action_url' => route('stories.show', $story),
            ],
            locale: $locale,
        );
    }
}
