<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->hasSystemRole('super_admin')) {
            return true;
        }

        return $user->hasSystemPermission('view_audit_logs');
    }
}
