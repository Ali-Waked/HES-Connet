<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read int $id
 * @property array $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 */
class Role extends Model
{
    use HasTranslations, HasUuids;
    public array $translatable = ['name'];
    protected $fillable = [
        'name',
        'slug',
        'scope',
        'description',
        'is_system',
        'is_active',
    ];

     public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeSystem(Builder $query): void
    {
        $query->where('scope', 'system');
    }

    public function scopeFacility(Builder $query): void
    {
        $query->where('scope', 'facility');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
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

        return '';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')->withTimestamps();
    }
}
