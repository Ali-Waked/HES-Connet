<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CheckEmailRequest;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffService $staff_service
    ) {}

    private function facilityStaff(): FacilityStaff
    {
        return auth()->user()->staff
            ->facilityStaff()
            ->whereNull('ended_at')
            ->firstOrFail();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $facility = $this->facilityStaff()->facility;

        info($this->staff_service->paginate(
            (int) request('per_page', 15),
            request('search'),
            $facility->uuid
        ));

        return StaffResource::collection(
            $this->staff_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                $facility->uuid
            )
        );
    }

    public function checkEmail(CheckEmailRequest $request): JsonResponse
    {
        return response()->json(
            $this->staff_service->checkEmail(
                $request->input('email')
            )
        );
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreStaffRequest $request): JsonResponse
    {
        $facility = $this->facilityStaff()->facility;

        $data = $request->validated();

        foreach ($data['facilities'] as &$facilityData) {
            $facilityData['facility_uuid'] = $facility->uuid;
        }

        $staff = $this->staff_service->create($data);

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
        $facilityId = $this->facilityStaff()->facility_id;

        abort_unless(
            $staff->facilityStaff()
                ->where('facility_id', $facilityId)
                ->whereNull('ended_at')
                ->exists(),
            403
        );

        return new StaffResource(
            $this->staff_service->show($staff)
        );
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateStaffRequest $request, Staff $staff): JsonResponse
    {
        $facility = $this->facilityStaff()->facility;

        abort_unless(
            $staff->facilityStaff()
                ->where('facility_id', $facility->id)
                ->whereNull('ended_at')
                ->exists(),
            403
        );

        $data = $request->validated();

        foreach ($data['facilities'] as &$facilityData) {
            $facilityData['facility_uuid'] = $facility->uuid;
        }

        $staff = $this->staff_service->update($staff, $data);

        return response()->json([
            'message' => __('Staff updated successfully.'),
            'data' => new StaffResource($staff),
        ]);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Staff $staff): JsonResponse
    {
        $facilityId = $this->facilityStaff()->facility_id;

        abort_unless(
            $staff->facilityStaff()
                ->where('facility_id', $facilityId)
                ->whereNull('ended_at')
                ->exists(),
            403
        );

        $this->staff_service->destroy($staff);

        return response()->json([
            'message' => __('Staff deleted successfully.'),
        ]);
    }

    public function terminate(FacilityStaff $staff): JsonResponse
    {
        abort_unless(
            $staff->facility_id === $this->facilityStaff()->facility_id,
            403
        );

        $staff->update([
            'ended_at' => now(),
        ]);

        return response()->json([
            'message' => __('Staff terminated successfully.'),
        ]);
    }

    public function lookup(): JsonResponse
    {
        $facilityId = $this->facilityStaff()->facility_id;

        $staff = FacilityStaff::with('staff.user')
            ->where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereDoesntHave('headedDepartment')
            ->get()
            ->map(function (FacilityStaff $facilityStaff) {
                return [
                    'uuid' => $facilityStaff->uuid,
                    'name' => $facilityStaff->staff->user->name,
                    'specialization' => $facilityStaff->staff->specialization,
                    'avatar' => $facilityStaff->staff->user->avatar,
                ];
            });

        return response()->json([
            'data' => $staff,
        ]);
    }
}
