<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Events\JobApproved;
use App\Events\JobPosted;
use App\Events\JobRejected;
use App\Models\Category;
use App\Models\JobPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class JobPostService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return $this->applyFilters(
            JobPost::query()->with(['facility', 'category']),
            $filters
        )->paginate($perPage);
    }

    public function publicPaginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return $this->applyFilters(
            JobPost::query()
                ->with(['facility', 'category'])
                ->approved()
                ->published()
                ->where(function (Builder $q) {
                    $q->whereDate('end_date', '>=', today())
                        ->orWhereNull('end_date');
                }),
            $filters
        )->paginate($perPage);
    }

    public function facilityPaginate(int $facilityId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return $this->applyFilters(
            JobPost::query()
                ->with(['facility', 'category'])
                ->where('facility_id', $facilityId),
            $filters
        )->paginate($perPage);
    }

    public function show(JobPost $jobPost): JobPost
    {
        return $jobPost->load(['facility', 'category', 'user']);
    }

    public function store(array $data): JobPost
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
                $data['cover_image'] = $data['cover_image']->store('job_posts/covers', 'public');
            }
            $data['category_id'] = Category::whereUuid($data['category_id'])->first()?->id;

            $jobPost = JobPost::create($data);

            event(new JobPosted($jobPost));

            return $jobPost;
        });
    }

    public function update(JobPost $jobPost, array $data): JobPost
    {
        return DB::transaction(function () use ($jobPost, $data) {
            if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
                $data['cover_image'] = $data['cover_image']->store('job_posts/covers', 'public');
            }
            $data['category_id'] = Category::whereUuid($data['category_id'])->first()?->id;

            $jobPost->update($data);

            return $jobPost->refresh();
        });
    }

    public function delete(JobPost $jobPost): void
    {
        $jobPost->delete();
    }

    public function approve(JobPost $jobPost): JobPost
    {
        return DB::transaction(function () use ($jobPost) {
            $jobPost->update([
                'status' => JobStatus::APPROVED,
                'published_at' => $jobPost->published_at ?? now(),
            ]);

            $jobPost = $jobPost->refresh();

            event(new JobApproved($jobPost));

            return $jobPost;
        });
    }

    public function reject(JobPost $jobPost, ?string $reason = null): JobPost
    {
        return DB::transaction(function () use ($jobPost, $reason) {
            $jobPost->update([
                'status' => JobStatus::REJECTED,
                'rejected_reason' => $reason,
            ]);

            $jobPost = $jobPost->refresh();

            event(new JobRejected($jobPost, $reason));

            return $jobPost;
        });
    }

    public function incrementViews(JobPost $jobPost): JobPost
    {
        $jobPost->increment('views');

        return $jobPost->refresh();
    }

    public function expireOldPosts(): int
    {
        return JobPost::query()
            ->where('status', JobStatus::APPROVED)
            ->whereDate('end_date', '<', today())
            ->update(['status' => JobStatus::EXPIRED]);
    }

    public function stats(?int $facilityId = null): array
    {
        $query = JobPost::query();

        if ($facilityId) {
            $query->where('facility_id', $facilityId);
        }

        $stats = (clone $query)
            ->selectRaw("
                COUNT(*) as total_posts,
                SUM(CASE WHEN status = 'approved' AND (end_date >= CURDATE() OR end_date IS NULL) AND published_at IS NOT NULL THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
            ")
            ->first();

        $thisMonth = (clone $query)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'total' => (int) $stats->total_posts,
            'approved' => (int) $stats->approved,
            'pending' => (int) $stats->pending,
            'rejected' => (int) $stats->rejected,
            'expired' => (int) $stats->expired,
            'active' => (int) $stats->active,
            'featured' => (int) $stats->featured,
            'today' => (int) $stats->today,
            'this_month' => $thisMonth,
        ];
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                $filters['search'] ?? null,
                fn (Builder $q, $search) => $q->where(function (Builder $q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
            )
            ->when(
                $filters['facility_id'] ?? null,
                fn (Builder $q, $facilityId) => $q->where('facility_id', $facilityId)
            )
            ->when(
                $filters['category_id'] ?? null,
                fn (Builder $q, $categoryId) => $q->where('category_id', $categoryId)
            )
            ->when(
                $filters['employment_type'] ?? null,
                fn (Builder $q, $type) => $q->where('employment_type', $type)
            )
            ->when(
                $filters['experience_level'] ?? null,
                fn (Builder $q, $level) => $q->where('experience_level', $level)
            )
            ->when(
                ($filters['featured'] ?? null) !== null,
                fn (Builder $q) => $q->where('featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN))
            )
            ->when(
                $filters['status'] ?? null,
                fn (Builder $q, $status) => $q->where('status', $status)
            )
            ->when(
                $filters['salary_from'] ?? null,
                fn (Builder $q, $salary) => $q->where('salary_from', '>=', $salary)
            )
            ->when(
                $filters['salary_to'] ?? null,
                fn (Builder $q, $salary) => $q->where('salary_to', '<=', $salary)
            )
            ->when(
                $filters['location'] ?? null,
                fn (Builder $q, $location) => $q->where('location', 'like', "%{$location}%")
            )
            ->when(
                $filters['created_from'] ?? null,
                fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date)
            )
            ->when(
                $filters['created_to'] ?? null,
                fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)
            )
            ->when(
                $filters['end_before'] ?? null,
                fn (Builder $q, $date) => $q->whereDate('end_date', '<=', $date)
            )
            ->when(
                $filters['end_after'] ?? null,
                fn (Builder $q, $date) => $q->whereDate('end_date', '>=', $date)
            )
            ->when(
                $filters['sort'] ?? null,
                fn (Builder $q, $sort) => match ($sort) {
                    'oldest' => $q->oldest(),
                    'most_viewed' => $q->orderByDesc('views'),
                    'ending_soon' => $q->orderBy('end_date'),
                    'featured' => $q->orderByDesc('featured')->latest(),
                    default => $q->latest(),
                }
            )
            ->unless($filters['sort'] ?? null, fn (Builder $q) => $q->latest());
    }
}
