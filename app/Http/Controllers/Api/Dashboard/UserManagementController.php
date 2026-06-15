<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\PatientListRequest;
use App\Http\Requests\UserManagement\StaffListRequest;
use App\Http\Resources\PatientListResource;
use App\Http\Resources\StaffListResource;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserManagementService $user_management_service)
    {
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->user_management_service->getStats()
        );
    }

    public function staff(StaffListRequest $request): JsonResource
    {
        return StaffListResource::collection(
            $this->user_management_service->paginateStaff(
                (int) $request->input('per_page', 15),
                $request->input('search'),
                $request->input('specialization'),
                $request->input('status'),
            )
        );
    }

    public function patients(PatientListRequest $request): JsonResource
    {
        return PatientListResource::collection(
            $this->user_management_service->paginatePatients(
                (int) $request->input('per_page', 15),
                $request->input('search'),
                $request->input('status'),
            )
        );
    }
}
