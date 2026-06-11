<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $staff_id
 * @property int $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property int $slot_duration
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 */
class DoctorSchedule extends Model
{
    protected $fillable = [
        'staff_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_duration' => 'integer',
            'start_time' => 'string',
            'end_time' => 'string',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
