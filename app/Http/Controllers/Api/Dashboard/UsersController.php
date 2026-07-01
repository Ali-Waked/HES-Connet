<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Users\StoreUserRequest;
use App\Http\Requests\API\Users\UpdateUserRequest;
use App\Http\Resources\API\UsersResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\UsersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UsersService $usersService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = $this->usersService->paginate(
            (int) $request->integer('per_page', 15),
            $request->input('search'),
            $request->only(['role', 'is_active', 'date_from', 'date_to']),
        );

        return $this->respond(UsersResource::collection($users));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->usersService->create($request->validated());

        return $this->respondCreated(
            new UsersResource($user),
            'User created successfully.'
        );
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user = $this->usersService->show($user);

        return $this->respond(new UsersResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user = $this->usersService->update($user, $request->validated());

        return $this->respondUpdated(
            new UsersResource($user),
            'User updated successfully.'
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->usersService->destroy($user);

        return $this->respondDeleted('User deleted successfully.');
    }

    public function restore(string $uuid): JsonResponse
    {
        $this->authorize('restore', User::class);

        $user = $this->usersService->restore($uuid);

        return $this->respondUpdated(
            new UsersResource($user),
            'User restored successfully.'
        );
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $this->authorize('toggleStatus', $user);

        $user = $this->usersService->toggleStatus($user);

        return $this->respondUpdated(
            new UsersResource($user),
            sprintf('User %s successfully.', $user->is_active ? 'activated' : 'deactivated')
        );
    }

    public function trashed(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::onlyTrashed()
            ->with(['systemRoles', 'profile'])
            ->latest('deleted_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->respond(UsersResource::collection($users));
    }
}
