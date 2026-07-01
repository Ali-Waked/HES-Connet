<?php

declare(strict_types=1);

namespace App\Notifications\Tags;

use App\Models\Tag;
use App\Notifications\BaseNotification;

class TagCreatedNotification extends BaseNotification
{
    public static function forAdmin(Tag $tag, ?string $locale = null): static
    {
        return new static(
            event: 'tag.created',
            role: 'admin',
            data: [
                'name' => $tag->getTranslations('name')['en'] ?? $tag->name,
                'action_text' => 'View Tag',
                'action_url' => route('dashboard.tags.show', $tag),
            ],
            locale: $locale,
        );
    }
}
