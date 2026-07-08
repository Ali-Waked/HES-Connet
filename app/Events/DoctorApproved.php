<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Staff;
use Illuminate\Foundation\Events\Dispatchable;

class DoctorApproved
{
    use Dispatchable;

    public function __construct(
        public readonly Staff $staff,
    ) {}
}
