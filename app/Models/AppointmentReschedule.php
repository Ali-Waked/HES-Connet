<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'appointment_id',
    'old_start_at',
    'old_end_at',
    'new_start_at',
    'new_end_at',
    'reason',
])]
class AppointmentReschedule extends Model
{
    use Auditable;

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
