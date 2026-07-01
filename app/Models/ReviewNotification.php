<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['review_id', 'type', 'sent_to', 'sent_at', 'payload'])]
class ReviewNotification extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(PlatformReview::class, 'review_id');
    }
}
