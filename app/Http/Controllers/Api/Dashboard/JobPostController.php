<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobPost\Dashboard\ApproveJobPostRequest;
use App\Http\Requests\JobPost\Dashboard\RejectJobPostRequest;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use App\Services\JobPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostController extends Controller
{
    public function __construct(
        private readonly JobPostService $jobPostService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $jobPosts = $this->jobPostService->paginate(
            $request->only([
                'per_page', 'search', 'facility_id', 'category_id',
                'employment_type', 'experience_level', 'featured', 'status',
                'salary_from', 'salary_to', 'location',
                'created_from', 'created_to', 'end_before', 'end_after', 'sort',
            ])
        );

        return JobPostResource::collection($jobPosts)->response();
    }

    public function show(JobPost $jobPost): JsonResponse
    {
        $jobPost = $this->jobPostService->show($jobPost);

        return response()->json([
            'data' => new JobPostResource($jobPost),
        ]);
    }

    public function approve(ApproveJobPostRequest $request, JobPost $jobPost): JsonResponse
    {
        $jobPost = $this->jobPostService->approve($jobPost);

        return response()->json([
            'message' => __('Job post approved successfully.'),
            'data' => new JobPostResource($jobPost),
        ]);
    }

    public function reject(RejectJobPostRequest $request, JobPost $jobPost): JsonResponse
    {
        $jobPost = $this->jobPostService->reject(
            $jobPost,
            $request->validated()['rejected_reason'] ?? null
        );

        return response()->json([
            'message' => __('Job post rejected successfully.'),
            'data' => new JobPostResource($jobPost),
        ]);
    }

    public function destroy(JobPost $jobPost): JsonResponse
    {
        $this->jobPostService->delete($jobPost);

        return response()->json([
            'message' => __('Job post deleted successfully.'),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(
            $this->jobPostService->stats()
        );
    }
}
