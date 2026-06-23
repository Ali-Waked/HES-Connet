<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\FacilityResource;
use App\Models\Staff;
use App\Services\StaffFacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffFacilityController extends Controller
{
    public function __construct(
        private readonly StaffFacilityService $facility_service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // if (!$user->hasSystemRole(['super_admin', 'facility_owner', 'organization_owner'])) {
        //     $userStaff = $user->staff()->firstOrFail();
        //     abort_if($userStaff->id !== $staff->id, 403, __('This action is unauthorized.'));
        // }

        $facilities = $this->facility_service->getFacilities($user->staff);

        return response()->json([
            'data' => FacilityResource::collection($facilities),
        ]);
    }
}
