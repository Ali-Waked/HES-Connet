<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformReview\StorePlatformReviewRequest;
use App\Http\Requests\PlatformReview\UpdatePlatformReviewRequest;
use App\Http\Resources\PlatformReviewResource;
use App\Models\PlatformReview;
use App\Services\PlatformReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformReviewController extends Controller
{
    public function __construct(
        private readonly PlatformReviewService $platform_review_service,
    ) {}

    public function myReview(Request $request): JsonResponse
    {
        $result = $this->platform_review_service->myReview($request->user());

        return response()->json([
            'message' => __('Review retrieved successfully.'),
            ...$result,
        ]);
    }

    public function store(StorePlatformReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        $eligibility = $this->platform_review_service->canUserReview($user);

        if (! $eligibility['can_review']) {
            return response()->json([
                'message' => __('You are not eligible to submit a review.'),
            ], 403);
        }

        if (PlatformReview::where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => __('You have already submitted a review.'),
            ], 409);
        }

        $review = $this->platform_review_service->store($user, $request->validated());

        return response()->json([
            'message' => __('Review submitted successfully.'),
            'data' => new PlatformReviewResource($review),
        ], 201);
    }

    public function update(UpdatePlatformReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        $eligibility = $this->platform_review_service->canUserReview($user);

        if (! $eligibility['can_review']) {
            return response()->json([
                'message' => __('You are not eligible to update a review.'),
            ], 403);
        }

        $review = PlatformReview::where('user_id', $user->id)->first();

        if (! $review) {
            return response()->json([
                'message' => __('No review found.'),
            ], 404);
        }

        $review->update($request->validated());

        return response()->json([
            'message' => __('Review updated successfully.'),
            'data' => new PlatformReviewResource($review->fresh()->load('repliedBy')),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        $review = PlatformReview::where('user_id', $user->id)->first();

        if (! $review) {
            return response()->json([
                'message' => __('No review found.'),
            ], 404);
        }

        $review->delete();

        return response()->json([
            'message' => __('Review deleted successfully.'),
        ]);
    }
}
