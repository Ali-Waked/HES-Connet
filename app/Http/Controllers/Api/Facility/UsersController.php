<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\UsersResource;
use App\Http\Responses\ApiResponse;
use App\Models\Facility;
use App\Models\User;
use App\Services\UsersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UsersService $usersService,
    ) {}

    public function index(Request $request, Facility $facility): JsonResponse
    {
        $this->authorize('viewFacility', $facility);

        $users = $this->usersService->paginateByFacility(
            $facility,
            (int) $request->integer('per_page', 15),
            $request->input('search'),
        );

        return $this->respond(UsersResource::collection($users));
    }

    public function show(Facility $facility, User $user): JsonResponse
    {
        $this->authorize('view', $user);
        $this->authorize('viewFacility', $facility);

        $user = $this->usersService->show($user);

        return $this->respond(new UsersResource($user));
    }
}
