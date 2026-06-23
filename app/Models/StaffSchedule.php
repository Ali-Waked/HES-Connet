<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffSchedule extends Model
{
    protected $fillable = [
        'facility_staff_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_duration' => 'integer',
            'start_time' => 'string',
            'end_time' => 'string',
            'is_active' => 'boolean',
        ];
    }

    public function facilityStaff(): BelongsTo
    {
        return $this->belongsTo(FacilityStaff::class);
    }
}
