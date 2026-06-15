<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FacilityApprovalStatus;
use App\Enums\FacilityStatus;
use App\Enums\FacilityType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * @property-read string $uuid
 * @property array $name
 * @property float $latitude
 * @property float $longitude
 * @property FacilityType $facility_type
 * @property FacilityStatus $status
 * @property FacilityApprovalStatus $approval_status
 * @property int|null $organization_id
 * @property int|null $parent_id
 * @property int|string|null $created_by
 * @property-read Organization|null $organization
 * @property-read Facility|null $parent
 * @property-read Collection<int, Facility> $children
 * @property-read Collection<int, FacilityImage> $facilityImages
 * @property-read Collection<int, FacilityDocument> $facilityDocuments
 * @property-read Collection<int, FacilityStaff> $facilityStaff
 * @property-read Collection<int, Department> $departments
 * @property-read Collection<int, Appointment> $appointments
 * @property-read Collection<int, PharmacyInventory> $pharmacyInventory
 * @property-read Collection<int, FacilityReview> $facilityReviews
 * @property-read Collection<int, JobPost> $jobPosts
 * @property-read Collection<int, MedicationRequest> $medicationRequests
 */
#[Fillable(['name', 'latitude', 'longitude', 'facility_type', 'organization_id', 'parent_id', 'created_by', 'status', 'approval_status', 'cover_image', 'city_id'])]
class Facility extends Model
{
    use HasTranslations, HasUuids;


    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'facility_type' => FacilityType::class,
            'status' => FacilityStatus::class,
            'approval_status' => FacilityApprovalStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Facility::class, 'parent_id');
    }

    public function headStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'head_staff_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function facilityImages(): HasMany
    {
        return $this->hasMany(FacilityImage::class);
    }

    public function facilityDocuments(): HasMany
    {
        return $this->hasMany(FacilityDocument::class);
    }

    public function facilityStaff(): HasMany
    {
        return $this->hasMany(FacilityStaff::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

       public function getCoverImageAttribute(?string $value): ?string
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }

    // public function appointments(): HasMany
    // {
    //     return $this->hasMany(Appointment::class);
    // }

    // public function pharmacyInventory(): HasMany
    // {
    //     return $this->hasMany(PharmacyInventory::class);
    // }

    // public function facilityReviews(): HasMany
    // {
    //     return $this->hasMany(FacilityReview::class);
    // }

    // public function jobPosts(): HasMany
    // {
    //     return $this->hasMany(JobPost::class);
    // }

    // public function medicationRequests(): HasMany
    // {
    //     return $this->hasMany(MedicationRequest::class);
    // }
}
