<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\FavoriteType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Favorite\ToggleFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly FavoriteService $favoriteService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $type = $request->filled('type')
            ? FavoriteType::from($request->string('type')->value())
            : null;

        $favorites = $this->favoriteService->paginate(
            $request->user(),
            $type,
            (int) $request->integer('per_page', 20),
        );

        return FavoriteResource::collection($favorites);
    }

    public function toggle(ToggleFavoriteRequest $request): JsonResponse
    {
        $result = $this->favoriteService->toggle(
            $request->user(),
            FavoriteType::from($request->validated('type')),
            $request->validated('id'),
        );

        return response()->json($result);
    }

    public function destroy(
        Favorite $favorite,
        Request $request,
    ): JsonResponse {
        abort_unless(
            $favorite->user_id === $request->user()->id,
            403,
            'Forbidden',
        );

        $this->favoriteService->destroy($favorite);

        return response()->json([
            'success' => true,
        ]);
    }
}
