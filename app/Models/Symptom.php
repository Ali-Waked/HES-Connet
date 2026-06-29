<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SymptomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable(['name'])]
#[Fillable(['name', 'is_active'])]
class Symptom extends Model
{
    /** @use HasFactory<SymptomFactory> */
    use HasFactory;

    use HasTranslations;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function facilityStaff(): BelongsToMany
    {
        return $this->belongsToMany(FacilityStaff::class, 'facility_staff_symptom')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
