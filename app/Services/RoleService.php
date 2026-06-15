<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
    use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleService
{
    public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function paginate(int $perPage = 15,?string $search): LengthAwarePaginator
    {
        return Role::query()
            ->with('permissions')
             ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                      ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Role
    {
        $role = Role::create($data);

        if (isset($data['permissions'])) {
            $permissions = [];
            foreach($data['permissions'] as $perm)
            $permissions[] = $this->uuid_resolver->resolve(
            Permission::class,
            $perm
        );
            $role->permissions()->sync($permissions);
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
            $permissionIds = collect($data['permissions'])
                ->map(fn ($uuid) => $this->uuid_resolver->resolve(
                    Permission::class,
                    $uuid
                ))
                ->toArray();

            $role->permissions()->sync($permissionIds);
        }
        return $role->refresh()->load('permissions');
    }

    public function destroy(Role $role): void
    {
        $role->delete();
    }


public function getStats(): array
{
    return [
        'total_roles' => Role::count(),

        'assigned_roles' => Role::has('users')->count(),

        'unassigned_roles' => Role::doesntHave('users')->count(),

        'total_permission_assignments' => DB::table('role_permission')->count(),
    ];
}
}
