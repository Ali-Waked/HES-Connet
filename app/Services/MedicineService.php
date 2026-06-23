<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MedicineService
{
    public function paginate(int $perPage = 15, ?string $search = null, string $sortBy = 'created_at', string $sortOrder = 'desc'): LengthAwarePaginator
    {
        return Medicine::query()
            ->when(
                $search,
                fn ($query) => $query->where('name', 'like', "%{$search}%")
            )
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function lookup(?string $search = null): Collection
    {
        return Medicine::query()
            ->select('uuid', 'name')
            ->when(
                $search,
                fn ($query) => $query->where('name', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Medicine
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image_url'] = $data['image']->store('medicines', 'public');
            }

            unset($data['image']);

            return Medicine::create($data);
        });
    }

    public function update(Medicine $medicine, array $data): Medicine
    {
        return DB::transaction(function () use ($medicine, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($medicine->getRawOriginal('image_url')) {
                    Storage::disk('public')->delete($medicine->getRawOriginal('image_url'));
                }

                $data['image_url'] = $data['image']->store('medicines', 'public');
            }

            unset($data['image']);

            $medicine->update($data);

            return $medicine->refresh();
        });
    }

    public function destroy(Medicine $medicine): void
    {
        DB::transaction(function () use ($medicine) {
            if ($medicine->getRawOriginal('image_url')) {
                Storage::disk('public')->delete($medicine->getRawOriginal('image_url'));
            }

            $medicine->delete();
        });
    }
}
