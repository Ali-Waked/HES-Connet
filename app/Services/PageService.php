<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
    ): LengthAwarePaginator {
        return Page::query()
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('title->en', 'like', "%{$search}%")
                        ->orWhere('title->ar', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                })
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Page
    {
        return Page::create($data);
    }

    public function update(Page $page, array $data): Page
    {
        $page->update($data);

        return $page->refresh();
    }

    public function destroy(Page $page): void
    {
        $page->delete();
    }
}
