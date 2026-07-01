<?php

declare(strict_types=1);

namespace App\Notifications\Categories;

use App\Models\Category;
use App\Notifications\BaseNotification;

class CategoryCreatedNotification extends BaseNotification
{
    public static function forAdmin(Category $category, ?string $locale = null): static
    {
        return new static(
            event: 'category.created',
            role: 'admin',
            data: [
                'name' => $category->getTranslations('name')['en'] ?? $category->name,
                'action_text' => 'View Category',
                'action_url' => route('dashboard.categories.show', $category),
            ],
            locale: $locale,
        );
    }
}
