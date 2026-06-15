<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FacilityStaff;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Role;
use App\Models\User;
use App\Models\Facility;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffService
{
     public function __construct(private readonly UuidResolver $uuid_resolver)
    {
    }

    public function paginate(int $perPage = 15, ?string $search = null, ?string $facilityUuid = null): LengthAwarePaginator
    {
        $facility = Facility::where('uuid', $facilityUuid)->first();

return Staff::query()
    ->with(['user', 'facilityStaff.facility', 'facilities', 'position'])

    ->when($search, function ($query) use ($search) {
        $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    })

    ->when($facility, function ($query) use ($facility) {
        $query->whereHas('facilityStaff', function ($q) use ($facility) {
            $q->where('facility_id', $facility->id);
        });
    })

    ->latest('id')
    ->paginate($perPage);
    }

    public function checkEmail(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return ['exists' => false];
        }

        return [
            'exists' => true,
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    public function create(array $data): Staff
{
    return DB::transaction(function () use ($data) {

        // Handle uploads safely
        if (!empty($data['cover_image'])) {
            $data['cover_image'] = $data['cover_image']->store('users/cover', 'public');
        }

        if (!empty($data['avatar'])) {
            $data['avatar'] = $data['avatar']->store('users/avatar', 'public');
        }

        // Find or create user
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make(Str::random(32)),
                'cover_image' => $data['cover_image'] ?? null,
                'avatar' => $data['avatar'] ?? null,
            ]);
        }

        // Prevent duplicate staff
        if (Staff::where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => __('A staff profile already exists for this user.'),
            ]);
        }

        // Create staff
        $staff = Staff::create([
            'user_id' => $user->id,
            'specialization' => $data['specialization'] ?? null,
            'experience_years' => $data['experience_years'] ?? null,
            'bio' => $data['bio'] ?? null,
            'consultation_fee' => $data['consultation_fee'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        // Facilities pivot
        collect($data['facilities'] ?? [])->each(function ($facilityData) use ($staff) {

            FacilityStaff::create([
                'staff_id' => $staff->id,

                'facility_id' => $this->uuid_resolver->resolve(
                    Facility::class,
                    $facilityData['facility_uuid']
                ),

                'position_id' => !empty($facilityData['position_uuid'])
                    ? $this->uuid_resolver->resolve(
                        Position::class,
                        $facilityData['position_uuid']
                    )
                    : null,

                'department_id' => !empty($facilityData['department_uuid'])
                    ? $this->uuid_resolver->resolve(
                        Department::class,
                        $facilityData['department_uuid']
                    )
                    : null,
            ]);
        });

        return $staff->load([
            'user',
            'facilityStaff.facility',
            'facilityStaff.position',
            'facilityStaff.department'
        ]);
    });
}
    public function show(Staff $staff): Staff
    {
        return $staff->load([
            'user',
            // 'facilityStaff.facility',
            'departmentsAsHead',
            // 'doctorSchedules',
            'facilities',
            'symptoms',
            'position',
        ]);
    }

    public function update(Staff $staff, array $data): Staff
    {
        if (isset($data['staff_position_uuid'])) {
            $data['staff_position_id'] = $this->uuid_resolver->resolve(
                StaffPosition::class,
                $data['staff_position_uuid']
            );
        }

        $staff->update($data);

        return $staff->load(['user', 'facilityStaff.facility', 'position']);
    }

    public function destroy(Staff $staff): void
    {
        $staff->delete();
    }
}
