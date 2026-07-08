<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Facility;
use Illuminate\Foundation\Events\Dispatchable;

class FacilityApproved
{
    use Dispatchable;

    public function __construct(
        public readonly Facility $facility,
    ) {}
}
