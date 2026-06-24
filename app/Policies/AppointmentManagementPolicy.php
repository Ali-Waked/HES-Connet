<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentManagementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('appointments.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('appointments.view');
    }

    public function cancel(User $user): bool
    {
        return $user->hasPermission('appointments.manage');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('appointments.manage');
    }

    public function forceComplete(User $user): bool
    {
        return $user->hasPermission('appointments.manage');
    }
}
