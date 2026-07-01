<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\PlatformReview;
use Illuminate\Foundation\Events\Dispatchable;

class PlatformReviewSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly PlatformReview $platformReview,
    ) {}
}
