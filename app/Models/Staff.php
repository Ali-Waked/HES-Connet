<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read string $uuid
 * @property int $user_id
 * @property array|null $specialization
 * @property int|null $experience_years
 * @property array|null $bio
 * @property float|null $consultation_fee
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FacilityStaff> $facilityStaff
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Department> $departmentsAsHead
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffSchedule> $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffUnavailability> $unavailabilities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointmentsAsDoctor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Prescription> $prescriptions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $articles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Symptom> $symptoms
 */
class Staff extends Model
{
    use HasUuids, HasTranslations;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'specialization',
        'experience_years',
        'bio',
        'consultation_fee',
        'status',
        'staff_position_id',
    ];

    public array $translatable = ['specialization', 'bio'];

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

    public function facilityStaff(): HasMany
    {
        return $this->hasMany(FacilityStaff::class);
    }

    public function departmentsAsHead(): HasMany
    {
        return $this->hasMany(Department::class, 'head_id');
    }

    // public function facilities(): BelongsToMany
    // {
    //     return $this->belongsToMany(Facility::class, 'facility_staff')
    //         ->withPivot('position')
    //         ->withTimestamps();
    // }

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
    ]);
}

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'facility_staff', 'staff_id', 'department_id')
            ->distinct();
    }

    public function headFacilities(): HasMany
    {
        return $this->hasMany(Facility::class, 'head_staff_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(StaffUnavailability::class);
    }

    public function appointmentsAsDoctor(): HasMany
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'auth_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'doctor_symptom', 'staff_id', 'symptom_id');
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
        return $query->whereHas('user.role', fn (Builder $q) => $q
            ->where('name->en', 'doctor')
        );
    }
}
