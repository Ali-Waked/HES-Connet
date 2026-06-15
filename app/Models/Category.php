<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoriesType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable(['name', 'description'])]
class Category extends Model
{
    use HasTranslations, HasUuids;

    // public array $translatable = ['name', 'description'];

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'is_active',
    ];

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
