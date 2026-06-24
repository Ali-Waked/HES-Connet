<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacilityDashboardPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        if ($user->hasSystemRole(['super_admin', 'facility_owner', 'facility_manager'])) {
            return true;
        }

        return $user->staff && $user->staff->facilityStaff()
            ->whereNull('ended_at')
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['facility_owner', 'facility_manager', 'department_manager']))
            ->exists();
    }
}
