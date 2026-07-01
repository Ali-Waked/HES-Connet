<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApplyMethod;
use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\JobStatus;
use App\Traits\Auditable;
use Database\Factories\JobPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable(['facility_id', 'user_id', 'category_id', 'slug', 'title', 'content', 'apply_method', 'apply_value', 'employment_type', 'experience_level', 'location', 'salary_from',
    'salary_to',
    'salary_currency',
    'is_salary_visible',
    'vacancies',
    'views',
    'featured',
    'cover_image',
    'status',
    'rejected_reason',
    'published_at',
    'end_date'])]
#[Translatable(['title', 'content'])]
class JobPost extends Model
{
    /** @use HasFactory<JobPostFactory> */
    use Auditable, HasFactory;

    use HasTranslations, HasUuids;

    protected function casts(): array
    {
        return [
            'apply_method' => ApplyMethod::class,
            'employment_type' => EmploymentType::class,
            'experience_level' => ExperienceLevel::class,
            'status' => JobStatus::class,
            'is_salary_visible' => 'boolean',
            'featured' => 'boolean',
            'salary_from' => 'decimal:2',
            'salary_to' => 'decimal:2',
            'vacancies' => 'integer',
            'views' => 'integer',
            'published_at' => 'datetime',
            'end_date' => 'date',
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

    protected static function booted(): void
    {
        static::creating(function (JobPost $jobPost) {
            if (! $jobPost->slug) {
                $jobPost->slug = Str::slug($jobPost->title).'-'.Str::lower(Str::random(6));
            }
        });
    }

    public function getCoverImageAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', JobStatus::APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', JobStatus::PENDING);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', JobStatus::REJECTED);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', JobStatus::EXPIRED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', JobStatus::APPROVED)
            ->where(function (Builder $query) {
                $query->whereDate('end_date', '>=', today())
                    ->orWhereNull('end_date');
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeExpiredEndDate(Builder $query): Builder
    {
        return $query->whereDate('end_date', '<', today());
    }
}
