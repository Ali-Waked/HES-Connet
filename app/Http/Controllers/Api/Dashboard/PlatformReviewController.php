<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformReview\AdminUpdatePlatformReviewRequest;
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

    public function index()
    {
        return PlatformReviewResource::collection(
            $this->platform_review_service->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('status'),
                request('rating') ? (int) request('rating') : null,
            )
        );
    }

    public function show(PlatformReview $platformReview): PlatformReviewResource
    {
        return new PlatformReviewResource(
            $this->platform_review_service->show($platformReview)
        );
    }

    public function reply(Request $request, PlatformReview $platformReview): JsonResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        $review = $this->platform_review_service->reply(
            $platformReview,
            $validated['reply'],
            $request->user()
        );

        return response()->json([
            'message' => __('Reply sent successfully.'),
            'data' => new PlatformReviewResource($review),
        ]);
    }

    public function update(AdminUpdatePlatformReviewRequest $request, PlatformReview $platformReview): JsonResponse
    {
        $platformReview = $this->platform_review_service->update($platformReview, $request->validated());

        return response()->json([
            'message' => __('Platform review updated successfully.'),
            'data' => new PlatformReviewResource($platformReview),
        ]);
    }

    public function destroy(PlatformReview $platformReview): JsonResponse
    {
        $this->platform_review_service->destroy($platformReview);

        return response()->json([
            'message' => __('Platform review deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->platform_review_service->getStats()
        );
    }
}
