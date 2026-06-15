<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string $uuid
 * @property array $name
 * @property int $facility_id
 * @property int|null $head_id
 * @property-read \App\Models\Facility $facility
 * @property-read \App\Models\Staff|null $head
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FacilityStaff> $facilityStaff
 */
#[Fillable(['name','facility_id','head_id','image','description','is_active'])]
#[Translatable(['name','description'])]
class Department extends Model
{
    use HasUuids, HasTranslations;

    protected function cats():array {
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
        return $this->belongsTo(Staff::class, 'head_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function facilityStaff(): HasMany
    {
        return $this->hasMany(FacilityStaff::class);
    }

    protected function image():Attribute {
        return Attribute::make( get: fn ($value) => $value ? Storage::disk('public')->url($value) : null);
    }
}
