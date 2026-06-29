<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoriesType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable(['uuid', 'name', 'description', 'type', 'is_active'])]
#[Translatable(['name', 'description'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasTranslations, HasUuids;

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'type' => CategoriesType::class,
            'is_active' => 'boolean',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
