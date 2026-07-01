<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Traits\Auditable;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $email
 * @property int|null $user_id
 * @property string $locale
 * @property Carbon|null $verified_at
 * @property bool $is_active
 * @property string $unsubscribe_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection<int, SubscriptionType> $subscriptionTypes
 */
#[Fillable(['email', 'user_id', 'locale', 'verified_at', 'is_active', 'unsubscribe_token'])]
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionTypes(): HasMany
    {
        return $this->hasMany(SubscriptionType::class);
    }

    public function hasType(string $type): bool
    {
        return $this->subscriptionTypes->contains('type', $type);
    }
}
