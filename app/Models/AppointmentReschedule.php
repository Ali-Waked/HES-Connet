<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentReschedule extends Model
{
    protected $fillable = [
        'appointment_id',
        'old_start_at',
        'old_end_at',
        'new_start_at',
        'new_end_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_start_at' => 'datetime',
            'old_end_at' => 'datetime',
            'new_start_at' => 'datetime',
            'new_end_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
