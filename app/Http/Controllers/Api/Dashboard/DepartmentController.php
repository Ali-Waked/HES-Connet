<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Facility;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $department_service
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return DepartmentResource::collection(
            $this->department_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('facility_id')
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        info('hi');
        $department = $this->department_service->dashboardCreate(
            $request->validated()
        );

        return response()->json([
            'message' => __('Department created successfully.'),
            'data' => new DepartmentResource(
                $department->load(['facilityStaff.facility', 'head'])
            ),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): JsonResponse
    {
        $department = $this->department_service->dashboardShow($department);

        return response()->json([
            'id' => $department->id,
            'uuid' => $department->uuid,
            'is_active' => $department->is_active,
            'facility_id' => $department->facility->uuid,
            'image' => $department->image,
            'name' => $department->getTranslations('name'),
            'description' => $department->getTranslations('description'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->department_service->dashboardUpdate(
            $department,
            $request->validated()
        );

        return response()->json([
            'message' => __('Department updated successfully.'),
            'data' => new DepartmentResource(
                $department->load(['facility', 'head'])
            ),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->department_service->dashboardDestroy($department);

        return response()->json([
            'message' => __('Department deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->department_service->getStats()
        );
    }

    public function lookup()
    {

        $facility = Facility::whereUuid(request()->facility_id)->firstOrFail();

        return DepartmentResource::collection(
            $facility->departments()
                ->where('is_active', true)
                ->orderBy('name->ar')
                ->get()
        );
    }
}
