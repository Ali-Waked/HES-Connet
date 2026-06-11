<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Permission::query()
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function show(Permission $permission): Permission
    {
        return $permission;
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission->refresh();
    }

    public function destroy(Permission $permission): void
    {
        $permission->delete();
    }
}
