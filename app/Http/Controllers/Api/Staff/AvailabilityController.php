<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\AvailableSlotResource;
use App\Models\Staff;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability_service,
    ) {}

    public function availableSlots(Request $request, Staff $staff): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'exists:facilities,uuid'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $slots = $this->availability_service->getAvailableSlots(
            $staff,
            $validated['facility_id'],
            $validated['date'],
        );

        return response()->json([
            'data' => AvailableSlotResource::collection(collect($slots)),
            'meta' => [
                'staff_id' => $staff->uuid,
                'date' => $validated['date'],
                'total' => count($slots),
            ],
        ]);
    }
}
