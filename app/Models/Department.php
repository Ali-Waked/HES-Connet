<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read string $uuid
 * @property array $name
 * @property int $id
 * @property int|null $head_facility_staff_id
 * @property-read FacilityStaff|null $head
 * @property-read Facility $facility
 * @property-read Collection<int, FacilityStaff> $facilityStaff
 */
#[Fillable(['name', 'head_facility_staff_id', 'image', 'description', 'is_active'])]
#[Translatable(['name', 'description'])]
class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    use HasTranslations, HasUuids;

    protected function casts(): array
    {
        return [
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

    public function head(): BelongsTo
    {
        return $this->belongsTo(FacilityStaff::class, 'head_facility_staff_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function facilityStaff(): HasMany
    {
        return $this->hasMany(FacilityStaff::class);
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')
                    ? $value
                    : Storage::disk('public')->url($value))
                : null
        );
    }
}
