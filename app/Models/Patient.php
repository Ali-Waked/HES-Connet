<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountStatus;
use App\Traits\Auditable;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property int $user_id
 * @property string|null $medical_history
 * @property-read User $user
 * @property-read Collection<int, Appointment> $appointments
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, Story> $stories
 * @property-read Collection<int, MedicationRequest> $medicationRequests
 * @property-read Collection<int, FacilityReview> $facilityReviews
 */
#[Fillable(['user_id', 'medical_history', 'status'])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use Auditable, HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function medicationRequests(): HasMany
    {
        return $this->hasMany(MedicationRequest::class);
    }

    public function facilityReviews(): HasMany
    {
        return $this->hasMany(FacilityReview::class);
    }

    protected function casts(): array
    {
        return [
            'status' => AccountStatus::class,
        ];
    }

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereHas('user', fn (Builder $q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
        );
    }
}
