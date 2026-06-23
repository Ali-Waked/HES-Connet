<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['systemRoles', 'systemRoles.permissions', 'profile', 'city', 'staff', 'patientProfile']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
