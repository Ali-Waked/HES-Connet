<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SearchHistoryService
{
    private const DEDUP_WINDOW_SECONDS = 60;

    public function logSearch(?User $user, string $query, ?string $type = null, ?array $filters = null): SearchHistory
    {
        if ($user) {
            $recent = SearchHistory::where('user_id', $user->id)
                ->where('query', $query)
                ->where('created_at', '>=', now()->subSeconds(self::DEDUP_WINDOW_SECONDS))
                ->latest()
                ->first();

            if ($recent) {
                $recent->touch();

                return $recent;
            }
        }

        return SearchHistory::create([
            'user_id' => $user?->id,
            'query' => $query,
            'type' => $type,
            'filters' => $filters,
        ]);
    }

    public function getUserHistory(
        User $user,
        ?string $type = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        return SearchHistory::where('user_id', $user->id)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate($perPage);
    }

    public function getTrendingSearches(int $limit = 10): Collection
    {
        return SearchHistory::query()
            ->select('query', DB::raw('COUNT(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    public function clearUserHistory(User $user, ?string $type = null): void
    {
        SearchHistory::where('user_id', $user->id)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->delete();
    }

    public function adminPaginate(
        ?string $type = null,
        ?string $search = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        return SearchHistory::with('user:id,uuid,name')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search, fn ($q) => $q->where('query', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }
}
