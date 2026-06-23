<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Models\FacilityReview;
use App\Services\FacilityPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly FacilityPortalService $portal_service,
    ) {}

    public function index(Request $request)
    {
        $facility = $this->portal_service->resolveFacility($request);

        $reviews = $this->portal_service->reviewsPaginate(
            $facility,
            (int) $request->get('per_page', 15),
            $request->has('is_visible') ? filter_var($request->get('is_visible'), FILTER_VALIDATE_BOOLEAN) : null,
        );

        return response()->json($reviews);
    }

    public function approve(Request $request, FacilityReview $review): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $review = $this->portal_service->approveReview($facility, $review);

        return response()->json([
            'message' => __('Review approved successfully.'),
            'data' => $review,
        ]);
    }

    public function reject(Request $request, FacilityReview $review): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);

        $review = $this->portal_service->rejectReview($facility, $review);

        return response()->json([
            'message' => __('Review rejected successfully.'),
            'data' => $review,
        ]);
    }
}
