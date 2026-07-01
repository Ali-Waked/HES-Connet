<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $subscription_id
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Subscription $subscription
 */
#[Fillable(['subscription_id', 'type'])]
class SubscriptionType extends Model
{
    use Auditable;

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
