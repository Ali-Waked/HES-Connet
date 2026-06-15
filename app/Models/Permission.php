<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 * @property string $key
 * @property array|null $name
 * @property array|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 */
class Permission extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
        ];
    }

    public function getLabel(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $name = $this->name;

        if (isset($name[$locale]) && $name[$locale] !== '') {
            return $name[$locale];
        }

        if (isset($name['en']) && $name['en'] !== '') {
            return $name['en'];
        }

        return $this->key;
    }

    public function getDescription(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $description = $this->description;

        if (isset($description[$locale]) && $description[$locale] !== '') {
            return $description[$locale];
        }

        if (isset($description['en']) && $description['en'] !== '') {
            return $description['en'];
        }

        return $this->key;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')->withTimestamps();
    }
}
