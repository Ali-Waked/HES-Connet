<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DonationStatus;
use App\Traits\Auditable;
use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['story_id', 'donor_id', 'amount', 'currency', 'status', 'paid_at'])]
class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use Auditable, HasFactory;

    use HasUuids;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => DonationStatus::class,
            'paid_at' => 'datetime',
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

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
