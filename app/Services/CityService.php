<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CityService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?bool $isActive = null,
    ): LengthAwarePaginator {
        return City::query()
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

    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(City $city, array $data): City
    {
        $city->update($data);

        return $city->refresh();
    }

    public function destroy(City $city): void
    {
        if ($city->users()->exists()) {
            throw ValidationException::withMessages([
                'city' => __('Cannot delete city with associated users.'),
            ]);
        }

        if ($city->facilities()->exists()) {
            throw ValidationException::withMessages([
                'city' => __('Cannot delete city with associated facilities.'),
            ]);
        }

        $city->delete();
    }
}
