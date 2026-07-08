<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicPlatformReviewResource;
use App\Services\PlatformReviewService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicPlatformReviewController extends Controller
{
    public function __construct(
        private readonly PlatformReviewService $platform_review_service,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return PublicPlatformReviewResource::collection(
            $this->platform_review_service->publicReviews()
        );
    }
}
