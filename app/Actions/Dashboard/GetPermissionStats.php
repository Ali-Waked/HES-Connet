<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class GetPermissionStats
{
    public function execute(): array
    {
        // Gate::authorize('permissions.view');

        return [
            'total_permissions' => Permission::count(),
            'total_modules' => (int) DB::table('permissions')
                ->selectRaw('COUNT(DISTINCT SUBSTRING_INDEX(`key`, ".", 1)) as total')
                ->value('total'),
            'total_roles' => Role::count(),
            'assigned_permissions' => DB::table('role_permission')->count(),
        ];
    }
}
