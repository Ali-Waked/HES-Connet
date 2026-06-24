<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('staff_schedules.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('staff_schedules.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('staff_schedules.manage');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('staff_schedules.manage');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('staff_schedules.manage');
    }
}
