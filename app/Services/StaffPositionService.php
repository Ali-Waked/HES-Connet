<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StaffPosition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class StaffPositionService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?bool $isActive = null,
    ): LengthAwarePaginator {
        return StaffPosition::query()
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $isActive !== null,
                fn ($query) => $query->where('is_active', $isActive)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): StaffPosition
    {
        return StaffPosition::create($data);
    }

    public function update(StaffPosition $staffPosition, array $data): StaffPosition
    {
        $staffPosition->update($data);

        return $staffPosition->refresh();
    }

    public function destroy(StaffPosition $staffPosition): void
    {
        if ($staffPosition->staff()->exists()) {
            throw ValidationException::withMessages([
                'staff_position' => __('Cannot delete position with associated staff members.'),
            ]);
        }

        $staffPosition->delete();
    }
}
