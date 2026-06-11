<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $role_service)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return RoleResource::collection(
            $this->role_service->paginate((int) request('per_page', 15))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->role_service->create($request->validated());

        return response()->json([
            'message' => __('Role created successfully.'),
            'data' => new RoleResource($role),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): RoleResource
    {
        return new RoleResource(
            $this->role_service->show($role)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->role_service->update($role, $request->validated());

        return response()->json([
            'message' => __('Role updated successfully.'),
            'data' => new RoleResource($role),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->role_service->destroy($role);

        return response()->json([
            'message' => __('Role deleted successfully.'),
        ]);
    }
}
