<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationUser\StoreOrganizationUserRequest;
use App\Http\Requests\OrganizationUser\UpdateOrganizationUserRequest;
use App\Http\Resources\OrganizationUserResource;
use App\Models\OrganizationUser;
use App\Services\OrganizationUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationUserController extends Controller
{
    public function __construct(private readonly OrganizationUserService $organization_user_service) {}

    /**
     * Display a listing of organization users.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $role = OrganizationRole::tryFrom(
            request('role', '')
        );

        return OrganizationUserResource::collection(
            $this->organization_user_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('organization_id') ? (int) request('organization_id') : null,
                $role
            )
        );
    }

    /**
     * Store a newly created organization user.
     *
     * @return JsonResponse
     */
    public function store(StoreOrganizationUserRequest $request): OrganizationUser
    {
        $organizationUser = $this->organization_user_service->create($request->validated());

        return $organizationUser;
    }

    /**
     * Display the specified organization user.
     */
    public function show(OrganizationUser $organizationUser): OrganizationUserResource
    {
        return new OrganizationUserResource(
            $this->organization_user_service->show($organizationUser)
        );
    }

    /**
     * Update the specified organization user.
     */
    public function update(UpdateOrganizationUserRequest $request, OrganizationUser $organizationUser): JsonResponse
    {
        $organizationUser = $this->organization_user_service->update(
            $organizationUser,
            $request->validated()
        );

        return response()->json([
            'message' => __('Organization user updated successfully.'),
            'data' => new OrganizationUserResource($organizationUser),
        ]);
    }

    /**
     * Remove the specified organization user.
     */
    public function destroy(OrganizationUser $organizationUser): JsonResponse
    {
        $this->organization_user_service->destroy($organizationUser);

        return response()->json([
            'message' => __('Organization user deleted successfully.'),
        ]);
    }
}
