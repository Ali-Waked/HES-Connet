<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformReviewStatus;
use App\Traits\Auditable;
use Database\Factories\PlatformReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'rating', 'comment', 'reply', 'replied_by', 'replied_at', 'is_featured', 'status'])]
class PlatformReview extends Model
{
    /** @use HasFactory<PlatformReviewFactory> */
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'replied_at' => 'datetime',
            'status' => PlatformReviewStatus::class,
        ];
    }

    public function editable(): Attribute
    {
        return Attribute::get(fn () => $this->status->isEditable());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ReviewNotification::class, 'review_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PlatformReviewStatus::APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeHidden(Builder $query): Builder
    {
        return $query->where('status', 'hidden');
    }

    public function hasAdminReply(): bool
    {
        return $this->admin_reply !== null;
    }

    public function isHighRating(): bool
    {
        return $this->rating >= 4;
    }

    public function isNeutralRating(): bool
    {
        return $this->rating === 3;
    }

    public function isLowRating(): bool
    {
        return $this->rating <= 2;
    }
}
