<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityReviewResource;
use App\Models\Facility;
use App\Models\FacilityReview;
use App\Services\FacilityReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FacilityReviewController extends Controller
{
    public function __construct(
        private readonly FacilityReviewService $facility_review_service,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return FacilityReviewResource::collection(
            $this->facility_review_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('is_visible'),
                request('facility'),
            )
        );
    }

    public function show(FacilityReview $facilityReview): FacilityReviewResource
    {
        return new FacilityReviewResource(
            $this->facility_review_service->show($facilityReview)
        );
    }

    public function hide(FacilityReview $facilityReview): JsonResponse
    {
        $facilityReview->update(['is_visible' => false]);

        return response()->json([
            'message' => __('Review hidden successfully.'),
            'data' => new FacilityReviewResource($facilityReview->load([
                'facility',
                'patient.user',
            ])),
        ]);
    }

    public function showReview(FacilityReview $facilityReview): JsonResponse
    {
        $facilityReview->update(['is_visible' => true]);

        return response()->json([
            'message' => __('Review shown successfully.'),
            'data' => new FacilityReviewResource($facilityReview->load([
                'facility',
                'patient.user',
            ])),
        ]);
    }

    public function stats(Facility $facility): JsonResponse
    {
        $reviews = FacilityReview::query()->where('facility_id', $facility->id);

        $stats = [
            'average_rating' => round(
                (float) $reviews->avg('rating'),
                1
            ),

            'total_reviews' => $reviews->count(),

            'rating_breakdown' => [
                '5' => (clone $reviews)->where('rating', 5)->count(),
                '4' => (clone $reviews)->where('rating', 4)->count(),
                '3' => (clone $reviews)->where('rating', 3)->count(),
                '2' => (clone $reviews)->where('rating', 2)->count(),
                '1' => (clone $reviews)->where('rating', 1)->count(),
            ],
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }
}
