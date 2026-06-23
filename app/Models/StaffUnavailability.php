<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffUnavailability extends Model
{
    protected $table = 'staff_unavailabilities';

    protected $fillable = [
        'facility_staff_id',
        'start_at',
        'end_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function facilityStaff(): BelongsTo
    {
        return $this->belongsTo(FacilityStaff::class);
    }
}
