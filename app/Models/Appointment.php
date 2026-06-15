<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'staff_id',
        'patient_id',
        'facility_id',
        'start_at',
        'end_at',
        'status',
        'notes',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'status' => AppointmentStatus::class,
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

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function reschedules(): HasMany
    {
        return $this->hasMany(AppointmentReschedule::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(AppointmentFile::class);
    }

    public function prescription(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
