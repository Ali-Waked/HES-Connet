<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FacilityDocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $facility_id
 * @property string $file_url
 * @property FacilityDocumentStatus $status
 * @property string $document_type
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Facility $facility
 */
class FacilityDocument extends Model
{
    protected $fillable = [
        'facility_id',
        'document_type',
        'status',
        'file_url',
    ];

    protected function casts(): array
    {
        return [
            'status' => FacilityDocumentStatus::class,
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
