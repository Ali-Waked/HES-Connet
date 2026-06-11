<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property int $user_id
 * @property string|null $medical_history
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appointment> $appointments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Story> $stories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicationRequest> $medicationRequests
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FacilityReview> $facilityReviews
 */
class Patient extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'medical_history',
    ];

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
}
