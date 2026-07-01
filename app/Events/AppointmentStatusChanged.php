<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;

class AppointmentStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}
}
