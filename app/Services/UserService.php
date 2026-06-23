<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->with(['systemRoles', 'profile', 'staff'])
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make('password');

        $roles = [];
        if (! empty($data['roles'])) {
            $roles = collect($data['roles'])->map(function (string $uuid) {
                return $this->uuid_resolver->resolve(Role::class, $uuid);
            })->toArray();
            unset($data['roles']);
        }

        $user = User::create($data);

        if (! empty($roles)) {
            $user->systemRoles()->sync($roles);
        }

        return $user->load(['systemRoles', 'profile', 'staff']);
    }

    public function show(User $user): User
    {
        return $user->load(['systemRoles', 'profile', 'staff']);
    }

    public function update(User $user, array $data): User
    {
        if (! empty($data['roles'])) {
            $roles = collect($data['roles'])->map(function (string $uuid) {
                return $this->uuid_resolver->resolve(Role::class, $uuid);
            })->toArray();
            $user->systemRoles()->sync($roles);
            unset($data['roles']);
        }

        $user->update($data);

        return $user->refresh()->load(['systemRoles', 'profile', 'staff']);
    }

    public function destroy(User $user): void
    {
        $user->delete();
    }
}
