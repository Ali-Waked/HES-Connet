<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\FacilityStaffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
#[Fillable(['staff_id', 'facility_id', 'department_id', 'position_id', 'position', 'role_id', 'joined_at', 'ended_at'])]
class FacilityStaff extends Pivot
{
    /** @use HasFactory<FacilityStaffFactory> */
    use Auditable, HasFactory;

    use HasUuids;

    protected $table = 'facility_staff';

    private const ADMIN_ROLES = [
        'facility_admin',
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

    public function headedDepartment()
    {
        return $this->hasOne(Department::class, 'head_facility_staff_id');
    }

    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(FacilityStaffPermission::class, 'facility_staff_id');
    }

    public function getEffectivePermissions(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        $overrides = $this->permissionOverrides;
        $overrides->loadMissing('permission');

        $disabledIds = $overrides->where('enabled', false)->pluck('permission_id')->toArray();

        $enabled = $overrides->where('enabled', true)
            ->map(fn (FacilityStaffPermission $override) => $override->permission)
            ->filter();

        return $this->role->permissions
            ->reject(fn (Permission $permission) => in_array($permission->id, $disabledIds, true))
            ->merge($enabled)
            ->unique('id')
            ->values();
    }

    public function getPermissionOverride(string $key): ?bool
    {
        $override = $this->permissionOverrides()
            ->whereHas('permission', fn ($query) => $query->where('key', $key))
            ->first();

        if (! $override) {
            return null;
        }

        return $override->enabled;
    }

    protected function isOwner(): Attribute
    {
        return Attribute::make(get: fn () => in_array($this->role->slug, self::ADMIN_ROLES, true));
    }
}
