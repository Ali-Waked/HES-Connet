<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PrescriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasUuids;

    protected $fillable = [
        'appointment_id',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PrescriptionStatus::class,
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function medicationRequests(): HasMany
    {
        return $this->hasMany(MedicationRequest::class);
    }
}
