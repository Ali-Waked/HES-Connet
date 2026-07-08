<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\Department\StoreDepartmentRequest;
use App\Http\Requests\Facility\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Facility;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $service,
    ) {}

    public function index(Facility $facility): AnonymousResourceCollection
    {
        return DepartmentResource::collection(
            $this->service->index($facility)
        );
    }

    public function store(
        StoreDepartmentRequest $request,
        Facility $facility,
    ): JsonResponse {
        $department = $this->service->store(
            $facility,
            $request->validated(),
        );

        return response()->json([
            'message' => __('Department created successfully.'),
            'data' => new DepartmentResource($department),
        ], 201);
    }

    public function show(
        Facility $facility,
        Department $department,
    ): DepartmentResource {
        return new DepartmentResource(
            $this->service->show($facility, $department)
        );
    }

    public function update(
        UpdateDepartmentRequest $request,
        Facility $facility,
        Department $department,
    ): DepartmentResource {
        return new DepartmentResource(
            $this->service->update(
                $facility,
                $department,
                $request->validated(),
            )
        );
    }

    public function destroy(
        Facility $facility,
        Department $department,
    ): JsonResponse {
        $this->service->destroy($facility, $department);

        return response()->json([], 204);
    }
}
