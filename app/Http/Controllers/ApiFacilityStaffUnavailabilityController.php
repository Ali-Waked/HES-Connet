<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaffUnavailabilityResource;
use App\Models\Facility;
use App\Models\StaffUnavailability;
use App\Services\FacilityStaffUnavailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffUnavailabilityController extends Controller
{
    public function __construct(
        private readonly FacilityStaffUnavailabilityService $service,
    ) {}

    public function index(
        Request $request,
        Facility $facility,
    ): AnonymousResourceCollection {
        return StaffUnavailabilityResource::collection(
            $this->service->index($facility, $request)
        );
    }

    public function show(
        Facility $facility,
        StaffUnavailability $staffUnavailability,
    ): StaffUnavailabilityResource {
        return new StaffUnavailabilityResource(
            $this->service->show($facility, $staffUnavailability)
        );
    }

    public function approve(
        Facility $facility,
        StaffUnavailability $staffUnavailability,
    ): JsonResponse {
        return response()->json([
            'message' => 'Request approved successfully.',
            'data' => new StaffUnavailabilityResource(
                $this->service->approve($facility, $staffUnavailability)
            ),
        ]);
    }

    public function reject(
        Facility $facility,
        StaffUnavailability $staffUnavailability,
    ): JsonResponse {
        return response()->json([
            'message' => 'Request rejected successfully.',
            'data' => new StaffUnavailabilityResource(
                $this->service->reject($facility, $staffUnavailability)
            ),
        ]);
    }
}
