<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $facility_id
 * @property string $image_url
 * @property-read \App\Models\Facility $facility
 */
class FacilityImage extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'facility_id',
        'image_url',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
      public function getImageUrlAttribute(?string $value): ?string
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }
}
