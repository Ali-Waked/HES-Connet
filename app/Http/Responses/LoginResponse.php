<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('Login successful.'),
            'user' => new UserResource($request->user()->load(['systemRoles', 'systemRoles.permissions', 'profile', 'city', 'staff'])),
        ]);
    }
}
