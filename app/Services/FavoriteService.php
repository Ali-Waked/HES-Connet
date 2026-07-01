<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FavoriteType;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class FavoriteService
{
    public function __construct(private readonly UuidResolver $uuidResolver) {}

    public function paginate(User $user, ?FavoriteType $type = null, int $perPage = 20): LengthAwarePaginator
    {
        return $user->favorites()
            ->with('favoritable')
            ->when(
                $type,
                fn ($query) => $query->where(
                    'favoritable_type',
                    $type->model(),
                ),
            )
            ->latest()
            ->paginate($perPage);
    }

    public function toggle(User $user, FavoriteType $type, string $uuid): array
    {
        $modelId = $this->uuidResolver->resolve($type->model(), $uuid);

        $favorite = $user->favorites()
            ->where('favoritable_type', $type->model())
            ->where('favoritable_id', $modelId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return [
                'success' => true,
                'favorite' => false,
            ];
        }

        $user->favorites()->create([
            'favoritable_type' => $type->model(),
            'favoritable_id' => $modelId,
        ]);

        return [
            'success' => true,
            'favorite' => true,
        ];
    }

    public static function isFavorited(
        int $userId,
        Model $model,
    ): bool {
        return Favorite::query()
            ->where('user_id', $userId)
            ->where('favoritable_type', $model::class)
            ->where('favoritable_id', $model->getKey())
            ->exists();
    }

    public function toggleModel(User $user, Model $model): array
    {
        $favorite = $user->favorites()
            ->where('favoritable_type', $model::class)
            ->where('favoritable_id', $model->getKey())
            ->first();

        if ($favorite) {
            $favorite->delete();

            return [
                'success' => true,
                'favorite' => false,
            ];
        }

        $user->favorites()->create([
            'favoritable_type' => $model::class,
            'favoritable_id' => $model->getKey(),
        ]);

        return [
            'success' => true,
            'favorite' => true,
        ];
    }

    public function destroy(Favorite $favorite): void
    {
        $favorite->delete();
    }
}
