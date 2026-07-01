<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\FacilityStaff;
use Illuminate\Foundation\Events\Dispatchable;

class StaffUnassigned
{
    use Dispatchable;

    public function __construct(
        public readonly FacilityStaff $facilityStaff,
    ) {}
}
