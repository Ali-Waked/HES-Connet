<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobPost\Public\IndexJobPostRequest;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use App\Services\JobPostService;
use Illuminate\Http\JsonResponse;

class JobPostController extends Controller
{
    public function __construct(
        private readonly JobPostService $jobPostService
    ) {}

    public function index(IndexJobPostRequest $request): JsonResponse
    {
        $jobPosts = $this->jobPostService->publicPaginate(
            $request->validated()
        );

        return JobPostResource::collection($jobPosts)->response();
    }

    public function show(string $slug): JobPostResource
    {
        $jobPost = JobPost::query()
            ->with(['facility', 'category'])
            ->approved()
            ->published()
            ->where('slug', $slug)
            ->whereDate('end_date', '>=', today())
            ->firstOrFail();

        $this->jobPostService->incrementViews($jobPost);

        return new JobPostResource($jobPost);
    }
}
