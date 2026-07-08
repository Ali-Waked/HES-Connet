<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FacilityDocumentStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read int $id
 * @property int $facility_id
 * @property string $file_url
 * @property FacilityDocumentStatus $status
 * @property string $document_type
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Facility $facility
 */
#[Fillable(['facility_id', 'document_type', 'status', 'file_url'])]
class FacilityDocument extends Model
{
    use Auditable;
    use HasUuids;

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
            'status' => FacilityDocumentStatus::class,
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function getFileUrlAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}
