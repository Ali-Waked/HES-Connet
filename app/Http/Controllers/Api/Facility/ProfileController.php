<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facility\UpdateProfileRequest;
use App\Http\Resources\Facility\ProfileResource;
use App\Models\Facility;
use App\Models\Staff;
use App\Services\FacilityPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly FacilityPortalService $portal_service,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $facility = $this->portal_service->resolveFacility($request);
        $user = $request->user();

        $user->load('profile', 'city');
        $staff = Staff::where('user_id', $user->id)->first();

        return response()->json([
            'data' => [
                'user' => $user,
                'facility' => $facility,
                'staff' => $staff,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $userData = array_intersect_key($validated, array_flip(['name', 'email']));
        if (!empty($userData)) {
            $user->update($userData);
        }

        $profileData = array_intersect_key($validated, array_flip(['phone', 'gender', 'birth_date', 'address']));
        if (!empty($profileData) && $user->profile) {
            $user->profile->update($profileData);
        }

        if (!empty($validated['avatar'])) {
            $path = $validated['avatar']->store('users/avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        if (!empty($validated['cover_image'])) {
            $path = $validated['cover_image']->store('users/covers', 'public');
            $user->update(['cover_image' => $path]);
        }

        $user->load('profile', 'city');
        $facility = $this->portal_service->resolveFacility($request);
        $staff = Staff::where('user_id', $user->id)->first();

        return response()->json([
            'message' => __('Profile updated successfully.'),
            'data' => [
                'user' => $user,
                'facility' => $facility,
                'staff' => $staff,
            ],
        ]);
    }
}
