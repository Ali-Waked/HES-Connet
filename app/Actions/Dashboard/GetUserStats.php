<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GetUserStats
{
    public function execute(): array
    {
        // Gate::authorize('viewUserManagement');

        return [
            'total_users' => User::count(),
            'total_staff' => Staff::count(),
            'total_patients' => Patient::count(),
            'online_now' => User::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
        ];
    }
}
