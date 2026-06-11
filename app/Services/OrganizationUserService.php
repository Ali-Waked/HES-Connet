<?php

namespace App\Services;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrganizationUserService
{
    /**
     * Paginate organization users with search and filters.
     */
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?int $organizationId = null,
        ?OrganizationRole $role = null,
    ): LengthAwarePaginator {
        return OrganizationUser::query()
            ->with([
                'organization',
                'user',
            ])
            ->when(
                $search,
                fn ($query) => $query->whereHas(
                    'user',
                    fn ($q) => $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->when(
                $organizationId,
                fn ($query) => $query->where('organization_id', $organizationId)
            )
            ->when(
                $role,
                fn ($query) => $query->where('status', $role->value)
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Eager-load relations for a single organization user.
     */
    public function show(OrganizationUser $organizationUser): OrganizationUser
    {
        return $organizationUser->load([
            'organization',
            'user',
        ]);
    }

    /**
     * Create a new organization user.
     */
    /**
     * Create a new organization user.
     */
    public function create(array $data): OrganizationUser
    {
        [$user, $organization] = $this->getUserAndOrganization(
            $data['user_id'],
            $data['organization_id']
        );

        $data['user_id'] = $user->id;
        $data['organization_id'] = $organization->id;
        $data['status'] = $data['role'];

        unset($data['role']);

        return OrganizationUser::create($data)
            ->load([
                'organization',
                'user',
            ]);
    }

    /**
     * Update an existing organization user.
     */
    public function update(
        OrganizationUser $organizationUser,
        array $data
    ): OrganizationUser {
        if (isset($data['user_id'], $data['organization_id'])) {
            [$user, $organization] = $this->getUserAndOrganization(
                $data['user_id'],
                $data['organization_id']
            );

            $data['user_id'] = $user->id;
            $data['organization_id'] = $organization->id;
        }

        if (isset($data['role'])) {
            $data['status'] = $data['role'];
            unset($data['role']);
        }

        $organizationUser->update($data);

        return $organizationUser->refresh()->load([
            'organization',
            'user',
        ]);
    }

    /**
     * Delete an organization user.
     */
    public function destroy(OrganizationUser $organizationUser): void
    {
        $organizationUser->delete();
    }

    /**
     * Resolve user and organization UUIDs to models.
     *
     * @return array{0: User, 1: Organization}
     */
    private function getUserAndOrganization(
        string $userUuid,
        string $organizationUuid
    ): array {
        $user = User::where('uuid', $userUuid)->firstOrFail();

        $organization = Organization::where(
            'uuid',
            $organizationUuid
        )->firstOrFail();

        return [$user, $organization];
    }
}
