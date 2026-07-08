<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Doctor\DoctorRequest;
use App\Http\Resources\Public\DoctorCollection;
use App\Http\Resources\Public\ShowDoctorResource;
use App\Models\Facility;
use App\Models\Staff;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availabilityService
    ) {}

    public function index(DoctorRequest $request): DoctorCollection
    {
        $doctors = Staff::query()
            ->with('facilities')
            ->withCount('facilities as facilities_count')
            ->doctors()
            ->when(
                $request->search,
                fn ($query, string $search) => $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($uq) => $uq
                        ->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%")
                    )
                        ->orWhereHas('specialization', fn ($sq) => $sq
                            ->where('name->en', 'like', "%{$search}%")
                            ->orWhere('name->ar', 'like', "%{$search}%")
                        )
                        ->orWhere('bio->en', 'like', "%{$search}%")
                        ->orWhere('bio->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->specialization,
                fn ($query, string $spec) => $query->whereHas('specialization', fn ($sq) => $sq
                    ->where('name->en', 'like', "%{$spec}%")
                    ->orWhere('name->ar', 'like', "%{$spec}%")
                )
            )
            ->when(
                $request->facility_id,
                fn ($query, string $uuid) => $query->whereHas('facilities', fn ($q) => $q->where('uuid', $uuid))
            )
            ->latest('id')
            ->paginate($request->per_page ?? 15);

        return new DoctorCollection($doctors);
    }

    public function show(Staff $staff): ShowDoctorResource
    {
        abort_unless(
            $staff->user->hasSystemRole('super_admin')
            || $staff->facilityStaff()->whereNull('ended_at')->whereHas('role', fn ($q) => $q->where('slug', 'doctor_portal_user'))->exists(),
            404
        );

        $staff->load(['facilities', 'departments', 'headFacilities']);

        return new ShowDoctorResource($staff);
    }

    public function availableDays(Facility $facility, Staff $staff): JsonResponse
    {
        $days = $this->availabilityService->getAvailableDays($facility, $staff);

        return response()->json($days);
    }

    public function availableSlots(Request $request, Facility $facility, Staff $staff): array
    {
        $slots = $this->availabilityService->getAvailableSlots(
            $staff,
            $facility->uuid,
            $request->query('date', now()->toDateString())
        );

        return array_map(fn (array $slot) => [
            'start_at' => $slot['start_at'],
            'end_at' => $slot['end_at'],
        ], $slots);
    }

    public function facilities(Staff $staff): JsonResponse
    {
        return response()->json($staff->facilities);
    }
}
