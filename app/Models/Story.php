<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DonationStatus;
use App\Enums\StoryStatus;
use App\Traits\Auditable;
use Database\Factories\StoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable(['patient_id', 'category_id', 'title', 'content', 'cover_image', 'status', 'is_fundraising', 'target_amount'])]
#[Translatable(['title', 'content'])]
class Story extends Model
{
    /** @use HasFactory<StoryFactory> */
    use Auditable, HasFactory;

    use HasTranslations, HasUuids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'status' => StoryStatus::class,
            'is_fundraising' => 'boolean',
        ];
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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function getCollectedAmountAttribute(): ?float
    {
        if (! $this->is_fundraising) {
            return null;
        }

        return (float) $this->donations()
            ->where('status', DonationStatus::COMPLETED)
            ->sum('amount');
    }
}
