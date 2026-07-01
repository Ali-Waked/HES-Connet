<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsersPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasSystemPermission('view_users');
    }

    public function view(User $user, User $target): bool
    {
        if ($user->hasSystemRole('super_admin')) {
            return true;
        }

        return $user->hasSystemPermission('show_user');
    }

    public function create(User $user): bool
    {
        return $user->hasSystemPermission('create_user');
    }

    public function update(User $user, User $target): bool
    {
        if ($user->hasSystemRole('super_admin')) {
            return true;
        }

        if ($target->hasSystemRole('super_admin') && ! $user->is($target)) {
            return false;
        }

        return $user->hasSystemPermission('update_user');
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        if ($target->hasSystemRole('super_admin') && ! $user->is($target)) {
            return false;
        }

        return $user->hasSystemPermission('delete_user');
    }

    public function restore(User $user): bool
    {
        return $user->hasSystemPermission('delete_user');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasSystemPermission('delete_user');
    }

    public function toggleStatus(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        return $user->hasSystemPermission('update_user');
    }

    public function viewFacility(User $user, Facility $facility): bool
    {
        $activeStaff = $user->getActiveFacilityStaff();

        if (! $activeStaff) {
            return $user->hasSystemRole('super_admin');
        }

        return $activeStaff->facility_id === $facility->id;
    }
}
