<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()
            ->with('permissions')
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Role
    {
        $role = Role::create($data);

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return $role->load('permissions');
    }

    public function show(Role $role): Role
    {
        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return $role->refresh()->load('permissions');
    }

    public function destroy(Role $role): void
    {
        $role->delete();
    }
}
