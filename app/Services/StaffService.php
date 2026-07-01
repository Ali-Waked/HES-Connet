<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\StaffAssigned;
use App\Events\StaffUnassigned;
use App\Models\Department;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Position;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StaffService
{
    public function __construct(private readonly UuidResolver $uuid_resolver) {}

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
        $user = User::query()
            ->select(['id', 'uuid', 'name', 'email'])
            ->with('profile')
            ->where('email', $email)
            ->first();

        if (! $user) {
            return [
                'exists' => false,
                'can_create_staff' => true,
            ];
        }

        $staff = Staff::where('user_id', $user->id)->first();

        $response = [
            'exists' => true,
            'has_staff_profile' => $staff !== null,
            'can_create_staff' => true,
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->getTranslations('name'),
                'email' => $user->email,
                'avatar' => $user->avatar,
                'cover_image' => $user->cover_image,
            ],
        ];

        if ($staff) {
            $response['staff'] = ['uuid' => $staff->uuid];
        }

        return $response;
    }

    public function create(array $data): Staff
    {
        return DB::transaction(function () use ($data) {

            $avatarPath = null;
            $coverPath = null;

            if (! empty($data['cover_image'])) {
                $coverPath = $data['cover_image']
                    ->store('users/cover', 'public');
            }

            if (! empty($data['avatar'])) {
                $avatarPath = $data['avatar']
                    ->store('users/avatar', 'public');
            }

            $user = User::firstOrCreate(
                [
                    'email' => $data['email'],
                ],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                array_filter([
                    'profile_image' => $avatarPath,
                    'cover_image' => $coverPath,
                ], fn ($v) => $v !== null)
            );
            $staff = Staff::firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'specialization' => $data['specialization'] ?? null,
                    'experience_years' => $data['experience_years'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'consultation_fee' => $data['consultation_fee'] ?? null,
                    'status' => $data['status'] ?? 'active',
                ]
            );

            foreach ($data['facilities'] ?? [] as $facilityData) {

                $facilityId = $this->uuid_resolver->resolve(
                    Facility::class,
                    $facilityData['facility_uuid']
                );

                $role = Role::query()
                    ->where('uuid', $facilityData['role_uuid'])
                    ->where('scope', 'facility')
                    ->where('is_active', true)
                    ->first();

                if (! $role) {
                    throw ValidationException::withMessages([
                        'role' => __('Invalid facility role selected.'),
                    ]);
                }

                $departmentId = ! empty($facilityData['department_uuid'])
                    ? $this->uuid_resolver->resolve(
                        Department::class,
                        $facilityData['department_uuid']
                    )
                    : null;

                $positionId = ! empty($facilityData['position_uuid'])
                    ? $this->uuid_resolver->resolve(
                        Position::class,
                        $facilityData['position_uuid']
                    )
                    : null;

                $exists = FacilityStaff::query()
                    ->where('staff_id', $staff->id)
                    ->where('facility_id', $facilityId)
                    ->whereNull('ended_at')
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'facility' => __('This staff member is already assigned to this facility.'),
                    ]);
                }

                $facilityStaff = FacilityStaff::create([
                    'staff_id' => $staff->id,
                    'facility_id' => $facilityId,
                    'department_id' => $departmentId,
                    'position_id' => $positionId,
                    'role_id' => $role->id,
                    'joined_at' => now(),
                    'ended_at' => null,
                ]);

                event(new StaffAssigned($facilityStaff));
            }

            return $staff->load([
                'user',
                'facilityStaff.role',
                'facilityStaff.facility',
                'facilityStaff.department',
                'facilityStaff.position',
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
            'position',
        ]);
    }

    public function update(Staff $staff, array $data): Staff
    {
        return DB::transaction(function () use ($staff, $data) {

            // 1. رفع الملفات الجديدة (وحذف القديمة من التخزين عند الاستبدال)
            $profilePayload = [];

            if (! empty($data['cover_image'])) {
                $oldImage = $staff->user->profile?->getRawOriginal('cover_image');
                if ($oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
                $profilePayload['cover_image'] = $data['cover_image']->store('users/cover', 'public');
            }

            if (! empty($data['avatar'])) {
                $oldImage = $staff->user->profile?->getRawOriginal('profile_image');
                if ($oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
                $profilePayload['profile_image'] = $data['avatar']->store('users/avatar', 'public');
            }

            if (! empty($profilePayload)) {
                $staff->user->profile()->updateOrCreate(
                    ['user_id' => $staff->user->id],
                    $profilePayload
                );
            }

            // 2. تحديث بيانات المستخدم المرتبط (فقط الحقول المرسلة فعلاً)
            $userPayload = array_intersect_key($data, array_flip(['name']));

            if (! empty($userPayload)) {
                $staff->user->update($userPayload);
            }

            // 3. تحويل staff_position_uuid إلى id
            if (isset($data['staff_position_uuid'])) {
                $data['staff_position_id'] = $this->uuid_resolver->resolve(
                    StaffPosition::class,
                    $data['staff_position_uuid']
                );
            }

            // 4. تحديث بيانات Staff (فقط الحقول المرسلة فعلاً)
            $staffPayload = array_intersect_key($data, array_flip([
                'specialization', 'experience_years', 'bio', 'consultation_fee', 'staff_position_id',
            ]));

            if (! empty($staffPayload)) {
                $staff->update($staffPayload);
            }

            // 5. مزامنة الـ facilities إذا تم إرسالها
            if (array_key_exists('facilities', $data)) {

                $incomingFacilityIds = [];

                foreach ($data['facilities'] as $facilityData) {

                    $facilityId = $this->uuid_resolver->resolve(
                        Facility::class,
                        $facilityData['facility_uuid']
                    );

                    $role = Role::query()
                        ->where('uuid', $facilityData['role_uuid'])
                        ->where('scope', 'facility')
                        ->where('is_active', true)
                        ->first();

                    if (! $role) {
                        throw ValidationException::withMessages([
                            'role' => __('Invalid facility role selected.'),
                        ]);
                    }

                    $departmentId = ! empty($facilityData['department_uuid'])
                        ? $this->uuid_resolver->resolve(Department::class, $facilityData['department_uuid'])
                        : null;

                    $positionId = ! empty($facilityData['position_uuid'])
                        ? $this->uuid_resolver->resolve(Position::class, $facilityData['position_uuid'])
                        : null;

                    $existing = FacilityStaff::query()
                        ->where('staff_id', $staff->id)
                        ->where('facility_id', $facilityId)
                        ->whereNull('ended_at')
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'department_id' => $departmentId,
                            'position_id' => $positionId,
                            'role_id' => $role->id,
                        ]);
                    } else {
                        $facilityStaff = FacilityStaff::create([
                            'staff_id' => $staff->id,
                            'facility_id' => $facilityId,
                            'department_id' => $departmentId,
                            'position_id' => $positionId,
                            'role_id' => $role->id,
                            'joined_at' => now(),
                            'ended_at' => null,
                        ]);

                        event(new StaffAssigned($facilityStaff));
                    }

                    $incomingFacilityIds[] = $facilityId;
                }

                // إنهاء التعيينات النشطة لأي facility لم تُرسل ضمن الطلب
                $endedRecords = FacilityStaff::query()
                    ->where('staff_id', $staff->id)
                    ->whereNotIn('facility_id', $incomingFacilityIds)
                    ->whereNull('ended_at')
                    ->get();

                foreach ($endedRecords as $existing) {
                    $existing->update(['ended_at' => now()]);
                    event(new StaffUnassigned($existing));
                }
            }

            return $staff->load([
                'user',
                'facilityStaff.role',
                'facilityStaff.facility',
                'facilityStaff.department',
                'facilityStaff.position',
            ]);
        });
    }

    public function destroy(Staff $staff): void
    {
        $staff->delete();
    }
}
