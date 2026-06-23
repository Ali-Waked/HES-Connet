<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;

class GetAppointmentStatsAction
{
    public function execute(): array
    {
        return [
            'total' => Appointment::count(),
            'today' => Appointment::whereDate('start_at', today())->count(),
            'upcoming' => Appointment::where('start_at', '>=', now())
                ->whereNotIn('status', [
                    AppointmentStatus::CANCELLED->value,
                    AppointmentStatus::NO_SHOW->value,
                    AppointmentStatus::COMPLETED->value,
                ])
                ->count(),
            'completed' => Appointment::where('status', AppointmentStatus::COMPLETED)->count(),
            'cancelled' => Appointment::where('status', AppointmentStatus::CANCELLED)->count(),
            'no_show' => Appointment::where('status', AppointmentStatus::NO_SHOW)->count(),
            'rescheduled' => Appointment::where('status', AppointmentStatus::RESCHEDULED)->count(),
        ];
    }
}
