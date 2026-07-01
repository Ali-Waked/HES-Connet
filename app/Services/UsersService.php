<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\City;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersService
{
    public function __construct() {}

    public function paginate(int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with(['systemRoles', 'profile', 'staff'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', fn ($p) => $p->where('phone', 'like', "%{$search}%"));
            }))
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->whereHas('systemRoles', fn ($r) => $r->where('slug', $role)))
            ->when($filters['is_active'] ?? null, fn ($q, $v) => $q->where('is_active', filter_var($v, FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $roles = $this->resolveRoles($data['roles'] ?? []);
            unset($data['roles']);

            if (! empty($data['city_id'])) {
                $data['city_id'] = $this->resolveCityId($data['city_id']);
            }

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = User::create($data);

            if (! empty($roles)) {
                $user->systemRoles()->sync($roles);
            }

            return $user->load(['systemRoles', 'profile', 'staff']);
        });
    }

    public function show(User $user): User
    {
        return $user->load(['systemRoles.permissions', 'profile', 'staff.facilityStaff.facility', 'city']);
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['roles'])) {
                $roles = $this->resolveRoles($data['roles']);
                $user->systemRoles()->sync($roles);
                unset($data['roles']);
            }

            if (! empty($data['city_id'])) {
                $data['city_id'] = $this->resolveCityId($data['city_id']);
            }

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            if (! empty($data)) {
                $user->update($data);
            }

            return $user->fresh()->load(['systemRoles', 'profile', 'staff']);
        });
    }

    public function destroy(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->delete();
        });
    }

    public function restore(string $uuid): User
    {
        return DB::transaction(function () use ($uuid) {
            $user = User::withTrashed()->where('uuid', $uuid)->firstOrFail();
            $user->restore();

            return $user->load(['systemRoles', 'profile', 'staff']);
        });
    }

    public function forceDelete(string $uuid): void
    {
        DB::transaction(function () use ($uuid) {
            $user = User::withTrashed()->where('uuid', $uuid)->firstOrFail();
            $user->forceDelete();
        });
    }

    public function toggleStatus(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        return $user->fresh();
    }

    public function paginateByFacility(Facility $facility, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->whereHas('staff.facilityStaff', fn ($q) => $q->where('facility_id', $facility->id)->whereNull('ended_at'))
            ->with(['systemRoles', 'profile', 'staff.facilityStaff' => fn ($q) => $q->where('facility_id', $facility->id)])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', fn ($p) => $p->where('phone', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate($perPage);
    }

    private function resolveRoles(array $roleUuids): array
    {
        return collect($roleUuids)
            ->map(fn (string $uuid) => Role::where('uuid', $uuid)->value('id'))
            ->filter()
            ->toArray();
    }

    private function resolveCityId(string $uuid): int
    {
        return City::where('uuid', $uuid)->valueOrFail('id');
    }
}
