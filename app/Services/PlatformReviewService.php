<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\PlatformReviewReplied;
use App\Events\PlatformReviewSubmitted;
use App\Models\PlatformReview;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PlatformReviewService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        ?int $rating = null,
    ): LengthAwarePaginator {
        return PlatformReview::query()
            ->with('user')
            ->when($search, fn ($query) => $query->whereHas('user', fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
            ))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($rating, fn ($query) => $query->where('rating', $rating))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): PlatformReview
    {
        return DB::transaction(function () use ($data) {
            $review = PlatformReview::create($data)->load('user');

            event(new PlatformReviewSubmitted($review));

            return $review;
        });
    }

    public function show(PlatformReview $platformReview): PlatformReview
    {
        return $platformReview->load(['user', 'repliedBy']);
    }

    public function update(PlatformReview $platformReview, array $data): PlatformReview
    {
        $platformReview->update($data);

        return $platformReview->refresh()->load(['user', 'repliedBy']);
    }

    public function destroy(PlatformReview $platformReview): void
    {
        $platformReview->delete();
    }

    public function reply(PlatformReview $review, string $adminReply, User $admin): PlatformReview
    {
        return DB::transaction(function () use ($review, $adminReply, $admin) {
            $review->update([
                'admin_reply' => $adminReply,
                'replied_by' => $admin->id,
                'replied_at' => now(),
                'status' => 'published',
            ]);

            $review->load('user');

            event(new PlatformReviewReplied($review, $adminReply, $admin));

            return $review->fresh()->load(['user', 'repliedBy']);
        });
    }

    public function getStats(): array
    {
        $stats = PlatformReview::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'approved' AND is_featured = 1 THEN 1 ELSE 0 END) as visible,
                SUM(CASE WHEN NOT (status = 'approved' AND is_featured = 1) THEN 1 ELSE 0 END) as hidden,
                COALESCE(AVG(rating), 0) as average_rating
            ")
            ->first();

        return [
            'total' => (int) $stats->total,
            'visible' => (int) $stats->visible,
            'hidden' => (int) $stats->hidden,
            'average_rating' => round((float) $stats->average_rating, 2),
        ];
    }
}
