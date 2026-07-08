<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PlatformReviewStatus;
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
                'status' => 'approved',
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

    public function canUserReview(User $user): array
    {
        if ($patient = $user->patient) {
            $hasCompletedAppointment = $patient->appointments()
                ->where('status', AppointmentStatus::COMPLETED)
                ->exists();

            if (! $hasCompletedAppointment) {
                return [
                    'can_review' => false,
                    'reason' => __('You need at least one completed appointment before submitting a review.'),
                ];
            }

            return [
                'can_review' => true,
                'reason' => null,
            ];
        }

        if ($staff = $user->staff) {
            $hasCompletedAppointment = $staff->appointmentsAsDoctor()
                ->where('status', AppointmentStatus::COMPLETED)
                ->exists();

            if (! $hasCompletedAppointment) {
                return [
                    'can_review' => false,
                    'reason' => __('You need at least one completed appointment before submitting a review.'),
                ];
            }

            return [
                'can_review' => true,
                'reason' => null,
            ];
        }

        return [
            'can_review' => false,
            'reason' => __('Only patients and healthcare providers can submit reviews.'),
        ];
    }

    public function myReview(User $user): array
    {
        $eligibility = $this->canUserReview($user);

        $review = PlatformReview::where('user_id', $user->id)->first();

        return [
            'can_review' => $eligibility['can_review'],
            'has_review' => $review !== null,
            'reason' => $eligibility['reason'],
            'review' => $review ? $review->load('repliedBy') : null,
        ];
    }

    public function publicReviews(): LengthAwarePaginator
    {
        return PlatformReview::query()
            ->with('user.patient')
            ->where('status', PlatformReviewStatus::APPROVED)
            ->orderBy('is_featured', 'desc')
            ->latest()
            ->paginate(15);
    }

    public function store(User $user, array $data): PlatformReview
    {
        return DB::transaction(function () use ($user, $data) {
            $review = PlatformReview::create([
                'user_id' => $user->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'status' => 'pending',
                'is_featured' => false,
            ])->load('repliedBy');

            event(new PlatformReviewSubmitted($review));

            return $review;
        });
    }

    public function destroyUserReview(User $user): void
    {
        PlatformReview::where('user_id', $user->id)->delete();
    }
}
