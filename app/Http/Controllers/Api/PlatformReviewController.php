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
        $review = PlatformReview::where('user_id', $request->user()->id)->first();

        if (! $review) {
            return response()->json([
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => new PlatformReviewResource($review->load('user')),
        ]);
    }

    public function store(StorePlatformReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        if (PlatformReview::where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => __('You have already submitted a review.'),
                'errors' => ['review' => [__('You can only create one review.')]],
            ], 422);
        }

        $review = $this->platform_review_service->create([
            'user_id' => $user->id,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
            'status' => 'pending',
            'is_featured' => false,
        ]);

        return response()->json([
            'message' => __('Review submitted successfully.'),
            'data' => new PlatformReviewResource($review),
        ], 201);
    }

    public function update(UpdatePlatformReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        $review = PlatformReview::where('user_id', $user->id)->first();

        if (! $review) {
            return response()->json([
                'message' => __('No review found.'),
            ], 404);
        }

        $review = $this->platform_review_service->update($review, [
            'rating' => $request->input('rating', $review->rating),
            'comment' => $request->input('comment', $review->comment),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => __('Review updated successfully.'),
            'data' => new PlatformReviewResource($review),
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
