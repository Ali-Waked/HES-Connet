<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $type = null,
        ?bool $isActive = null
    ): LengthAwarePaginator {
        return Category::query()
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                      ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $type,
                fn ($query) => $query->where('type', $type)
            )
            ->when(
                $isActive !== null,
                fn ($query) => $query->where('is_active', $isActive)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    public function destroy(Category $category): void
    {
        $category->delete();
    }

    public function getStats(): array
    {
        $stats = Category::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(type = 'article') as articles,
                SUM(type = 'story') as stories,
                SUM(type = 'job') as jobs,
                SUM(is_active = 1) as active,
                SUM(is_active = 0) as inactive
            ")
            ->first();

        return [
            'total' => (int) $stats->total,
            'articles' => (int) $stats->articles,
            'stories' => (int) $stats->stories,
            'jobs' => (int) $stats->jobs,
            'active' => (int) $stats->active,
            'inactive' => (int) $stats->inactive,
        ];
    }
}
