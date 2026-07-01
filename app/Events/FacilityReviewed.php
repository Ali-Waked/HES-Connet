<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\FacilityReview;
use Illuminate\Foundation\Events\Dispatchable;

class FacilityReviewed
{
    use Dispatchable;

    public function __construct(
        public readonly FacilityReview $facilityReview,
    ) {}
}
