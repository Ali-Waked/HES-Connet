<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Doctor\DoctorRequest;
use App\Http\Resources\Public\DoctorCollection;
use App\Http\Resources\Public\ShowDoctorResource;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;

class DoctorController extends Controller
{
    public function index(DoctorRequest $request): DoctorCollection
    {
        $doctors = Staff::query()
            ->with('facilities')
            ->withCount('facilities as facilities_count')
            ->doctors()
            ->when(
                $request->search,
                fn (Builder $query, string $search) => $query->where(function (Builder $q) use ($search) {
                    $q->whereHas('user', fn (Builder $uq) => $uq
                        ->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%")
                    )
                    ->orWhere('specialization->en', 'like', "%{$search}%")
                    ->orWhere('specialization->ar', 'like', "%{$search}%")
                    ->orWhere('bio->en', 'like', "%{$search}%")
                    ->orWhere('bio->ar', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->specialization,
                fn (Builder $query, string $spec) => $query->where(function (Builder $q) use ($spec) {
                    $q->where('specialization->en', 'like', "%{$spec}%")
                      ->orWhere('specialization->ar', 'like', "%{$spec}%");
                })
            )
            ->when(
                $request->facility_id,
                fn (Builder $query, string $uuid) => $query->whereHas('facilities', fn (Builder $q) => $q
                    ->where('uuid', $uuid)
                )
            )
            ->latest('id')
            ->paginate($request->per_page ?? 15);

        return new DoctorCollection($doctors);
    }

    public function show(Staff $staff): ShowDoctorResource
    {
        abort_unless($staff->user->role?->name['en'] === 'doctor', 404);

        $staff->load([
            'facilities',
            'departments',
            'headFacilities',
        ]);

        return new ShowDoctorResource($staff);
    }
}
