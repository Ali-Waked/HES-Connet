<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $facility_id
 * @property string $image_url
 * @property-read \App\Models\Facility $facility
 */
class FacilityImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'facility_id',
        'image_url',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
