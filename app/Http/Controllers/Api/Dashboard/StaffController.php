<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CheckEmailRequest;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;

class StaffController extends Controller
{
    public function __construct(private readonly StaffService $staff_service)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return StaffResource::collection(
            $this->staff_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('facility_id')
            )
        );
    }

    public function checkEmail(CheckEmailRequest $request): JsonResponse
    {
        return response()->json(
            $this->staff_service->checkEmail($request->input('email'))
            );
            }

            /**
             * Store a newly created resource in storage.
            */
            public function store(StoreStaffRequest $request): JsonResponse
            {
        info('hi in controller');
        $staff = $this->staff_service->create($request->validated());

        return response()->json([
            'message' => __('Staff created successfully.'),
            'data' => new StaffResource($staff),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff): StaffResource
    {
        return new StaffResource(
            $this->staff_service->show($staff)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaffRequest $request, Staff $staff): JsonResponse
    {
        $staff = $this->staff_service->update($staff, $request->validated());

        return response()->json([
            'message' => __('Staff updated successfully.'),
            'data' => new StaffResource($staff),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff): JsonResponse
    {
        $this->staff_service->destroy($staff);

        return response()->json([
            'message' => __('Staff deleted successfully.'),
        ]);
    }
}
