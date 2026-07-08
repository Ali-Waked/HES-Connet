<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PrescriptionRoute;
use App\Traits\Auditable;
use Database\Factories\PrescriptionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['prescription_id', 'medicine_id', 'dosage', 'frequency', 'duration', 'route', 'instructions', 'quantity'])]
class PrescriptionItem extends Model
{
    /** @use HasFactory<PrescriptionItemFactory> */
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return [
            'route' => PrescriptionRoute::class,
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
