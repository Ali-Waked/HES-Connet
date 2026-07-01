<?php

namespace App\Http\Controllers\Api\Facility;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request, Facility $facility)
    {
        $facilityStaff = auth()->user()?->staff?->facilityStaff()
            ->where('facility_id', $facility->id)
            ->first();

        if (! $facilityStaff) {
            return response()->json([
                'message' => 'Unauthorized facility access.',
            ], 403);
        }

        // Base permission check
        // $isOwner = $facilityStaff->role->slug === 'facility_admin';

        // Base query depending on context
        $query = Patient::query()
            ->with([
                'user',
                'appointments.facilityStaff.staff.user',
            ])
            ->whereHas('appointments.facilityStaff', function ($q) use ($facility) {
                $q->where('facility_id', $facility->id);
            });

        /**
         * =========================
         * CONTEXT RULES
         * =========================
         */
        if (! $facilityStaff->is_owner) {
            // Doctor / Staff → only their patients
            $query->whereHas('appointments', function ($q) use ($facilityStaff) {
                $q->where('facility_staff_id', $facilityStaff->id);
            });
        }

        /**
         * =========================
         * FILTERS
         * =========================
         */

        // Search (name / phone)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Staff filter (ONLY for owner or permissioned users)
        if ($facilityStaff->is_owner && $request->filled('facility_staff_id')) {
            $query->whereHas('appointments', function ($q) use ($request) {
                $q->where('facility_staff_id', $request->facility_staff_id);
            });
        }

        // Status filter (if patient has status field later)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /**
         * =========================
         * RESULT
         * =========================
         */
        $query->withMax('appointments', 'start_at');
        $patients = $query
            ->orderByDesc('appointments_max_start_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($patients);
    }
}
