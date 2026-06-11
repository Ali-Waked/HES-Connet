<?php

namespace App\Services;

use App\Enums\OrganizationType;
use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrganizationService
{
    public function paginate(int $perPage = 15, ?string $search = null, ?OrganizationType $type = null): LengthAwarePaginator
    {
        return Organization::query()
            ->when(
                $search,
                fn ($query) => $query->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
            )
            ->when(
                $type,
                fn ($query) => $query->where(
                    'type',
                    $type->value
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Organization
    {
        return Organization::create($data);
    }

    public function show(Organization $organization): Organization
    {
        return $organization;
    }

    public function update(Organization $organization, array $data): Organization
    {
        $organization->update($data);

        return $organization->refresh();
    }

    public function destroy(Organization $organization): void
    {
        $organization->delete();
    }
}
