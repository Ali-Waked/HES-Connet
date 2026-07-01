<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Actions\Dashboard\GetPermissionStats;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permission_service,
        private readonly GetPermissionStats $getPermissionStats,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return PermissionResource::collection(
            $this->permission_service->paginate((int) request('per_page', 15), request('search'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permission_service->create($request->validated());

        return response()->json([
            'message' => __('Permission created successfully.'),
            'data' => new PermissionResource($permission),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission): PermissionResource
    {
        return new PermissionResource(
            $this->permission_service->show($permission)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission = $this->permission_service->update($permission, $request->validated());

        return response()->json([
            'message' => __('Permission updated successfully.'),
            'data' => new PermissionResource($permission),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->permission_service->destroy($permission);

        return response()->json([
            'message' => __('Permission deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getPermissionStats->execute(),
        ]);
    }
}
