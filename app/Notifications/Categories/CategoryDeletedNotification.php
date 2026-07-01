<?php

declare(strict_types=1);

namespace App\Notifications\Categories;

use App\Models\Category;
use App\Notifications\BaseNotification;

class CategoryDeletedNotification extends BaseNotification
{
    public static function forAdmin(Category $category, ?string $locale = null): static
    {
        return new static(
            event: 'category.deleted',
            role: 'admin',
            data: [
                'name' => $category->getTranslations('name')['en'] ?? $category->name,
            ],
            locale: $locale,
        );
    }
}
