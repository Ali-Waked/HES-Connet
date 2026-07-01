<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_stories');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('show_story');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_story');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update_story');
    }

    public function delete(User $user): bool
    {
        return $user->hasSystemRole('super_admin');
    }

    public function updateStatus(User $user): bool
    {
        return $user->hasSystemRole('super_admin');
    }

    public function viewTrash(User $user): bool
    {
        return $user->hasSystemRole('super_admin');
    }

    public function restore(User $user): bool
    {
        return $user->hasSystemRole('super_admin');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasSystemRole('super_admin');
    }
}
