<?php

declare(strict_types=1);

namespace App\Notifications\Stories;

use App\Models\Story;
use App\Notifications\BaseNotification;

class StoryCreatedNotification extends BaseNotification
{
    public static function forPatient(Story $story, ?string $locale = null): static
    {
        return new static(
            event: 'story.created',
            role: 'patient',
            data: [
                'title' => $story->title,
                'patient' => $story->patient?->name ?? 'A patient',
                'action_text' => 'View Story',
                'action_url' => route('stories.show', $story),
            ],
            locale: $locale,
        );
    }

    public static function forAdmin(Story $story, ?string $locale = null): static
    {
        return new static(
            event: 'story.created',
            role: 'admin',
            data: [
                'title' => $story->title,
                'patient' => $story->patient?->name ?? 'A patient',
                'action_text' => 'View Story',
                'action_url' => route('stories.show', $story),
            ],
            locale: $locale,
        );
    }
}
