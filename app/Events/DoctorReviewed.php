<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Review;
use Illuminate\Foundation\Events\Dispatchable;

class DoctorReviewed
{
    use Dispatchable;

    public function __construct(
        public readonly Review $review,
    ) {}
}
