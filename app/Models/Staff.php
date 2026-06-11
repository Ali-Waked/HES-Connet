<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DoctorSchedule> $doctorSchedules
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DoctorUnavailable> $unavailableDates
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
    ];

    public array $translatable = ['specialization', 'bio'];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'consultation_fee' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facilityStaff(): HasMany
    {
        return $this->hasMany(FacilityStaff::class);
    }

    public function departmentsAsHead(): HasMany
    {
        return $this->hasMany(Department::class, 'head_id');
    }

    public function doctorSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(DoctorUnavailable::class);
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

    public function symptoms(): HasMany
    {
        return $this->belongsToMany(Symptom::class, 'symptom_doctor', 'staff_id', 'symptom_id');
    }
}
