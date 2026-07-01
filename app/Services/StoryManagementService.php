<?php

namespace App\Services;

use App\Enums\DonationStatus;
use App\Enums\StoryStatus;
use App\Events\StoryApproved;
use App\Events\StoryRejected;
use App\Models\Donation;
use App\Models\Story;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StoryManagementService
{
    public function getStories(array $filters): LengthAwarePaginator
    {
        return Story::query()
            ->with(['patient.user', 'category'])
            ->when(
                $filters['status'] ?? null,
                fn ($q, $v) => $q->where('status', $v)
            )
            ->when(
                isset($filters['is_fundraising']),
                fn ($q) => $q->where('is_fundraising', (bool) $filters['is_fundraising'])
            )
            ->when(
                $filters['patient_id'] ?? null,
                fn ($q, $v) => $q->where('patient_id', $v)
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($q, $v) => $q->where('category_id', $v)
            )
            ->when(
                $filters['search'] ?? null,
                fn ($q) => $q->where(function ($q) use ($filters) {
                    $search = $filters['search'];
                    $q->where('title->ar', 'like', "%{$search}%")
                        ->orWhere('title->en', 'like', "%{$search}%")
                        ->orWhereHas('patient.user', fn ($q) => $q->where('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%"));
                })
            )
            ->when(
                $filters['date_from'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '>=', $v)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($q, $v) => $q->whereDate('created_at', '<=', $v)
            )
            ->when(
                $filters['sort'] ?? null,
                fn ($q, $v) => $v === 'oldest' ? $q->oldest() : $q->latest(),
                fn ($q) => $q->latest()
            )
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function getTrash(array $filters): LengthAwarePaginator
    {
        return Story::onlyTrashed()
            ->with(['patient.user', 'category'])
            ->when(
                $filters['search'] ?? null,
                fn ($q) => $q->where(function ($q) use ($filters) {
                    $search = $filters['search'];
                    $q->where('title->ar', 'like', "%{$search}%")
                        ->orWhere('title->en', 'like', "%{$search}%")
                        ->orWhereHas('patient.user', fn ($q) => $q->where('name->ar', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%"));
                })
            )
            ->when(
                $filters['date_from'] ?? null,
                fn ($q, $v) => $q->whereDate('deleted_at', '>=', $v)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($q, $v) => $q->whereDate('deleted_at', '<=', $v)
            )
            ->when(
                $filters['patient_id'] ?? null,
                fn ($q, $v) => $q->where('patient_id', $v)
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($q, $v) => $q->where('category_id', $v)
            )
            ->latest('deleted_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function getStats(): array
    {
        $stats = Story::query()
            ->selectRaw("
                COUNT(*) as total_stories,
                SUM(status = 'pending') as pending_stories,
                SUM(status = 'approved') as approved_stories,
                SUM(status = 'rejected') as rejected_stories,
                SUM(is_fundraising = 1) as fundraising_stories,
                COALESCE(SUM(target_amount), 0) as total_target_amount
            ")
            ->first();

        $totalCollected = Donation::where('status', DonationStatus::COMPLETED)->sum('amount');
        $trashed = Story::onlyTrashed()->count();

        return [
            'total_stories' => (int) $stats->total_stories,
            'pending_stories' => (int) $stats->pending_stories,
            'approved_stories' => (int) $stats->approved_stories,
            'rejected_stories' => (int) $stats->rejected_stories,
            'fundraising_stories' => (int) $stats->fundraising_stories,
            'total_target_amount' => (float) $stats->total_target_amount,
            'total_collected_amount' => (float) $totalCollected,
            'trashed_stories' => $trashed,
        ];
    }

    public function show(Story $story): Story
    {
        return $story->load(['patient.user', 'category']);
    }

    public function showTrashed(string $id): Story
    {
        return Story::onlyTrashed()->with(['patient.user', 'category'])->findOrFail($id);
    }

    public function updateStatus(Story $story, string $status): Story
    {
        $story->update([
            'status' => StoryStatus::from($status),
        ]);

        $story = $story->fresh();

        if ($story->status->value === 'approved') {
            event(new StoryApproved($story));
        } elseif ($story->status->value === 'rejected') {
            event(new StoryRejected($story));
        }

        return $story;
    }

    public function delete(Story $story): void
    {
        $story->delete();
    }

    public function restore(string $id): Story
    {
        $story = Story::onlyTrashed()->where('uuid', $id)->firstOrFail();
        $story->restore();

        return $story->load(['patient.user', 'category']);
    }

    public function forceDelete(string $id): void
    {
        $story = Story::onlyTrashed()->findOrFail($id);
        $story->forceDelete();
    }
}
