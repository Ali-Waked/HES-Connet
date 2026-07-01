<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StaffUnavailabilityStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['facility_staff_id', 'start_at', 'end_at', 'reason'])]
class StaffUnavailability extends Model
{
    use Auditable;

    protected $table = 'staff_unavailabilities';

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'status' => StaffUnavailabilityStatus::class,
        ];
    }

    public function facilityStaff(): BelongsTo
    {
        return $this->belongsTo(FacilityStaff::class);
    }
}
