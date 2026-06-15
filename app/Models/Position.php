<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Translatable\HasTranslations;

class Position extends Model
{
    use HasTranslations,HasUuids;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

  public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
    public function facilityStaff(): HasMany
    {
        return $this->hasMany(FacilityStaff::class);
    }
}
