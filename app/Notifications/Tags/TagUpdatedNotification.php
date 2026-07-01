<?php

declare(strict_types=1);

namespace App\Notifications\Tags;

use App\Models\Tag;
use App\Notifications\BaseNotification;

class TagUpdatedNotification extends BaseNotification
{
    public static function forAdmin(Tag $tag, ?string $locale = null): static
    {
        return new static(
            event: 'tag.updated',
            role: 'admin',
            data: [
                'name' => $tag->getTranslations('name')['en'] ?? $tag->name,
            ],
            locale: $locale,
        );
    }
}
