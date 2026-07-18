<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobPost\Dashboard\StoreJobPostRequest;
use App\Http\Requests\JobPost\Dashboard\UpdateJobPostRequest;
use App\Http\Resources\JobPostResource;
use App\Models\Facility;
use App\Models\JobPost;
use App\Services\JobPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostController extends Controller
{
    public function __construct(
        private readonly JobPostService $jobPostService,
    ) {}

    public function index(Request $request, Facility $facility): JsonResponse
    {
        // $facility = $this->getFacility($request);
        // $this->ensureFacilityOwner($facility);

        $jobPosts = $this->jobPostService->facilityPaginate(
            $facility->id,
            $request->only([
                'per_page', 'search', 'category_id',
                'employment_type', 'experience_level', 'status',
                'salary_from', 'salary_to', 'location',
                'created_from', 'created_to', 'end_before', 'end_after', 'sort',
            ])
        );

        return JobPostResource::collection($jobPosts)->response();
    }

    public function show(Request $request, JobPost $jobPost): JsonResponse
    {
        $facility = $this->getFacility($request);
        // $this->ensureFacilityOwner($facility);
        // $this->ensurePostBelongsToFacility($jobPost, $facility);

        $jobPost = $this->jobPostService->show($jobPost);

        return response()->json([
            'data' => new JobPostResource($jobPost),
        ]);
    }

    public function store(StoreJobPostRequest $request, Facility $facility): JsonResponse
    {
        // $facility = $this->getFacility($request);
        // $this->ensureFacilityOwner($facility);

        $data = array_merge($request->validated(), [
            'facility_id' => $facility->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        $jobPost = $this->jobPostService->store($data);

        return response()->json([
            'message' => __('Job post created successfully.'),
            'data' => new JobPostResource($jobPost),
        ], 201);
    }

    public function update(UpdateJobPostRequest $request, JobPost $jobPost): JsonResponse
    {
        $facility = $this->getFacility($request);
        $this->ensureFacilityOwner($facility);
        $this->ensurePostBelongsToFacility($jobPost, $facility);

        $jobPost = $this->jobPostService->update($jobPost, $request->validated());

        return response()->json([
            'message' => __('Job post updated successfully.'),
            'data' => new JobPostResource($jobPost),
        ]);
    }

    public function destroy(Request $request, JobPost $jobPost): JsonResponse
    {
        $facility = $this->getFacility($request);
        $this->ensureFacilityOwner($facility);
        $this->ensurePostBelongsToFacility($jobPost, $facility);

        $this->jobPostService->delete($jobPost);

        return response()->json([
            'message' => __('Job post deleted successfully.'),
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $facility = $this->getFacility($request);
        $this->ensureFacilityOwner($facility);

        return response()->json(
            $this->jobPostService->stats($facility->id)
        );
    }

    private function getFacility(Request $request): Facility
    {
        $user = $request->user();
        $facility = $user->activeWorkspace?->load('owner');

        if (! $facility) {
            abort(403, __('No active facility workspace.'));
        }

        return $facility;
    }

    private function ensureFacilityOwner(Facility $facility): void
    {
        $userId = request()->user()->id;
        info($facility->appointments->toArray());

        $isAdmin = $facility->facilityStaff()
            ->whereHas('staff', fn ($q) => $q->where('user_id', $userId))
            ->whereHas('role', fn ($q) => $q->where('slug', 'facility_admin'))
            ->exists();

        if (! $isAdmin) {
            abort(403, __('You do not own this facility.'));
        }
    }

    private function ensurePostBelongsToFacility(JobPost $jobPost, Facility $facility): void
    {
        if ($jobPost->facility_id !== $facility->id) {
            abort(404, __('Job post not found.'));
        }
    }
}
