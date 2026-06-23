<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffUnavailability\StoreStaffUnavailabilityRequest;
use App\Http\Requests\StaffUnavailability\UpdateStaffUnavailabilityRequest;
use App\Http\Resources\StaffUnavailabilityResource;
use App\Models\StaffUnavailability;
use App\Services\StaffUnavailabilityService;
use Illuminate\Http\JsonResponse;

class StaffUnavailabilityController extends Controller
{
    public function __construct(private readonly StaffUnavailabilityService $staff_unavailability_service)
    {
    }

    public function index()
    {
        return StaffUnavailabilityResource::collection(
            $this->staff_unavailability_service->paginate(
                (int) request('per_page', 15),
                request('facility_staff_id')
            )
        );
    }

    public function store(StoreStaffUnavailabilityRequest $request): JsonResponse
    {
        $staffUnavailability = $this->staff_unavailability_service->create($request->validated());

        return response()->json([
            'message' => __('Staff unavailability created successfully.'),
            'data' => new StaffUnavailabilityResource($staffUnavailability),
        ], 201);
    }

    public function show(StaffUnavailability $staffUnavailability): StaffUnavailabilityResource
    {
        return new StaffUnavailabilityResource(
            $this->staff_unavailability_service->show($staffUnavailability)
        );
    }

    public function update(UpdateStaffUnavailabilityRequest $request, StaffUnavailability $staffUnavailability): JsonResponse
    {
        $staffUnavailability = $this->staff_unavailability_service->update($staffUnavailability, $request->validated());

        return response()->json([
            'message' => __('Staff unavailability updated successfully.'),
            'data' => new StaffUnavailabilityResource($staffUnavailability),
        ]);
    }

    public function destroy(StaffUnavailability $staffUnavailability): JsonResponse
    {
        $this->staff_unavailability_service->destroy($staffUnavailability);

        return response()->json([
            'message' => __('Staff unavailability deleted successfully.'),
        ]);
    }
}
