<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
       public function __construct(
        private readonly DepartmentService $department_service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->department_service->paginate(
                request('per_page', 15),
                request('search'),
                request('facility_id')
            )
        );
    }


    /**
     * Store a newly created resource in storage.
     */
 
    public function store( StoreDepartmentRequest $request): JsonResponse 
    {

        $department = $this->department_service->create(
            $request->validated()
        );

        return response()->json([
            'message' => __('Department created successfully.'),
            'data' => $department->load([
                'facility',
                'head',
            ]),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): Department 
    {

        return $this->department_service->show($department);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
            $department = $this->department_service->update(
            $department,
            $request->validated()
        );

        return response()->json([
            'message' => __('Department updated successfully.'),
            'data' => $department->load([
                'facility',
                'head',
            ]),
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): JsonResponse 
    {

        $this->department_service->destroy($department);

        return response()->json([
            'message' => __('Department deleted successfully.'),
        ]);
    }
}
