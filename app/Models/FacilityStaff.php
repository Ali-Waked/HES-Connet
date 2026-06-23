<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property-read int $id
 * @property int $staff_id
 * @property int $facility_id
 * @property int|null $department_id
 * @property string $position
 * @property-read Staff $staff
 * @property-read Facility $facility
 * @property-read Department|null $department
 */
class FacilityStaff extends Pivot
{
    use HasUuids;

    protected $table = 'facility_staff';

    protected $fillable = [
        'staff_id',
        'facility_id',
        'department_id',
        'position_id',
        'position',
        'role_id',
        'joined_at',
        'ended_at',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('ended_at');
    }

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

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class, 'facility_staff_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'facility_staff_id');
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(StaffUnavailability::class, 'facility_staff_id');
    }
}
