<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Actions\Dashboard\GetUserStats;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserSelectResource;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $user_service,
        private readonly GetUserStats $getUserStats,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return UserResource::collection(
            $this->user_service->paginate(
                (int) request('per_page', 15),
                request('search')
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->user_service->create($request->validated());

        return response()->json([
            'message' => __('User created successfully.'),
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        return new UserResource(
            $this->user_service->show($user)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->user_service->update($user, $request->validated());

        return response()->json([
            'message' => __('User updated successfully.'),
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->user_service->destroy($user);

        return response()->json([
            'message' => __('User deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getUserStats->execute(),
        ]);
    }

    public function select(): AnonymousResourceCollection
    {
        $query = User::query()->select(['id', 'uuid', 'name', 'email']);

        if ($roleSlug = request('role')) {
            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $query->whereHas('systemRoles', fn ($q) => $q->where('slug', $roleSlug));
            }
        }

        return UserSelectResource::collection($query->latest()->get());
    }
}
