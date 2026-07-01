<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function setActiveWorkspace(Request $request, Facility $facility): JsonResponse
    {
        $user = $request->user();

        // check if user has access to this facility
        $hasAccess = $user->staff()
            ->whereHas('facilityStaff', function ($q) use ($facility) {
                $q->where('facility_id', $facility->id)
                    ->whereNull('ended_at');
            })
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'You do not have access to this facility.',
            ], 403);
        }

        // set active workspace
        $user->active_workspace_id = $facility->id;
        $user->save();

        // reload with relation
        $user->load('activeWorkspace');

        return response()->json([
            'message' => 'Active workspace updated successfully.',
            'active_workspace' => $user->activeWorkspace,
            'permissions' => $user->getAllPermissions()->pluck('key'),
        ]);
    }
}
