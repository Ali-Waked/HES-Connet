<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $staff_id
 * @property int $facility_id
 * @property int|null $department_id
 * @property string $position
 * @property-read \App\Models\Staff $staff
 * @property-read \App\Models\Facility $facility
 * @property-read \App\Models\Department|null $department
 */
class FacilityStaff extends Model
{
    protected $fillable = [
        'staff_id',
        'facility_id',
        'department_id',
        'position',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
