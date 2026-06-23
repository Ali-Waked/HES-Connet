<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PlatformReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
        return PlatformReview::create($data)->load('user');
    }

    public function show(PlatformReview $platformReview): PlatformReview
    {
        return $platformReview->load('user');
    }

    public function update(PlatformReview $platformReview, array $data): PlatformReview
    {
        $platformReview->update($data);

        return $platformReview->refresh()->load('user');
    }

    public function destroy(PlatformReview $platformReview): void
    {
        $platformReview->delete();
    }

    public function getStats(): array
    {
        $stats = PlatformReview::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'approved') as approved,
                SUM(status = 'rejected') as rejected
            ")
            ->first();

        return [
            'total' => (int) $stats->total,
            'pending' => (int) $stats->pending,
            'approved' => (int) $stats->approved,
            'rejected' => (int) $stats->rejected,
        ];
    }
}
