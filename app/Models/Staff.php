<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read string $uuid
 * @property int $user_id
 * @property array|null $specialization
 * @property int|null $experience_years
 * @property array|null $bio
 * @property float|null $consultation_fee
 * @property-read User $user
 * @property-read Collection<int, FacilityStaff> $facilityStaff
 * @property-read Collection<int, Department> $departmentsAsHead
 * @property-read Collection<int, StaffSchedule> $schedules
 * @property-read Collection<int, StaffUnavailability> $unavailabilities
 * @property-read Collection<int, Appointment> $appointmentsAsDoctor
 * @property-read Collection<int, Prescription> $prescriptions
 * @property-read Collection<int, Article> $articles
 * @property-read Collection<int, Review> $reviews
 */
#[Fillable(['user_id', 'profession_id', 'specialization', 'experience_years', 'bio', 'consultation_fee', 'status', 'staff_position_id'])]
#[Translatable(['specialization', 'bio'])]
class Staff extends Model
{
    /** @use HasFactory<StaffFactory> */
    use HasFactory, HasTranslations, HasUuids;

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'consultation_fee' => 'decimal:2',
            'status' => AccountStatus::class,
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'staff_position_id');
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    public function facilityStaff(): HasMany
    {
        return $this->hasMany(FacilityStaff::class);
    }

    public function departmentsAsHead(): HasMany
    {
        return $this->hasMany(Department::class, 'head_facility_staff_id');
    }

    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'facility_staff'
        )
            ->using(FacilityStaff::class)
            ->withPivot([
                'position_id',
                'department_id',
                'role_id',
                'joined_at',
                'ended_at',
                'uuid',
            ]);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'facility_staff',
            'staff_id',
            'department_id'
        )->whereNotNull('department_id')->distinct();
    }

    public function headFacilities(): HasMany
    {
        return $this->hasMany(Facility::class, 'head_staff_id');
    }

    public function schedules(): HasManyThrough
    {
        return $this->hasManyThrough(
            StaffSchedule::class, // Final Model
            FacilityStaff::class, // Intermediate Model

            'staff_id',           // FK on facility_staff
            'facility_staff_id',  // FK on staff_schedules

            'id',                 // PK on staff
            'id'                  // PK on facility_staff
        );
    }

    public function unavailabilities(): HasManyThrough
    {
        return $this->hasManyThrough(StaffUnavailability::class, FacilityStaff::class);
    }

    public function appointmentsAsDoctor(): HasManyThrough
    {
        return $this->hasManyThrough(
            Appointment::class,
            FacilityStaff::class,
            'staff_id',
            'facility_staff_id',
            'id',
            'id'
        );
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'auth_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeOfSpecialization(Builder $query, string $specialization): Builder
    {
        return $query->where('specialization', 'like', "%{$specialization}%");
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereHas('user', fn (Builder $q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
        );
    }

    public function scopeDoctors(Builder $query): Builder
    {
        return $query->whereHas('facilityStaff', fn (Builder $q) => $q
            ->whereNull('ended_at')
            ->whereHas('role', fn (Builder $rq) => $rq->where('slug', 'doctor_portal_user'))
        );
    }

    public function getAvailableWorkspaces(): array
    {
        return $this->staff
            ->facilityStaff
            ->map(function ($membership) {
                return [
                    'id' => $membership->id, // facility_staff.id
                    'facility' => [
                        'id' => $membership->facility->id,
                        'uuid' => $membership->facility->uuid,
                        'name' => $membership->facility->name,
                    ],
                    'role' => [
                        'id' => $membership->role->id,
                        'slug' => $membership->role->slug,
                        'name' => $membership->role->name,
                    ],
                ];
            })
            ->values()
            ->toArray();
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
