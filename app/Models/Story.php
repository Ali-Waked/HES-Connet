<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StoryStatus;
use Database\Factories\StoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable(['patient_id', 'category_id', 'title', 'content', 'cover_image', 'status', 'is_fundraising', 'target_amount', 'collected_amount'])]
#[Translatable(['title', 'content'])]
class Story extends Model
{
    /** @use HasFactory<StoryFactory> */
    use HasFactory;

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
}
