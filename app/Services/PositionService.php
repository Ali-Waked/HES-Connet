<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PositionService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?bool $status = null
    ): LengthAwarePaginator {
        info(request()->all());
        return Position::query()
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $status !== null,
                fn ($query) => $query->where('is_active', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Position
    {
        return Position::create($data);
    }

    public function update(Position $position, array $data): Position
    {
        $position->update($data);

        return $position->refresh();
    }

    public function destroy(Position $position): void
    {
        if ($position->facilityStaff()->exists()) {
            throw new \RuntimeException(
                __('Cannot delete position because it is assigned to staff members.')
            );
        }

        $position->delete();
    }

    public function getStats(): array
    {
        $stats = Position::query()
            ->selectRaw('
                COUNT(*) as total,
                SUM(is_active = 1) as active,
                SUM(is_active = 0) as inactive
            ')
            ->first();

        return [
            'total' => (int) $stats->total,
            'active' => (int) $stats->active,
            'inactive' => (int) $stats->inactive,
        ];
    }
}
