<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Position;
use App\Models\Role;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilityStaffSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->keyBy('slug');
        $facilities = Facility::all();
        $departments = Department::all();
        $positions = Position::pluck('id')->toArray();

        $doctorStaff = Staff::whereHas('user', function ($q) {
            $q->whereIn('email', ['ahmad@example.com', 'lina@example.com', 'mohammed@example.com', 'sarah@example.com']);
        })->get();

        // Assign doctors to facilities
        foreach ($doctorStaff as $index => $staff) {
            $facility = $facilities[$index % $facilities->count()];
            $department = $departments->random();

            FacilityStaff::create([
                'uuid' => Str::uuid(),
                'staff_id' => $staff->id,
                'facility_id' => $facility->id,
                'department_id' => $department->id,
                'position_id' => ! empty($positions) ? $positions[array_rand($positions)] : null,
                'role_id' => $roles->get('doctor_portal_user')?->id,
                'joined_at' => now()->subYears(fake()->numberBetween(1, 5))->format('Y-m-d'),
                'ended_at' => null,
            ]);
        }

        // Assign admin staff to facilities
        $adminEmails = ['layla@example.com', 'hani@example.com', 'dina@example.com', 'samir@example.com'];
        $adminStaff = Staff::whereHas('user', function ($q) use ($adminEmails) {
            $q->whereIn('email', $adminEmails);
        })->get();

        foreach ($adminStaff as $staff) {
            $facility = $facilities->random();
            FacilityStaff::create([
                'uuid' => Str::uuid(),
                'staff_id' => $staff->id,
                'facility_id' => $facility->id,
                'department_id' => $departments->random()->id,
                'position_id' => ! empty($positions) ? $positions[array_rand($positions)] : null,
                'role_id' => $roles->get('facility_admin')?->id,
                'joined_at' => now()->subYears(fake()->numberBetween(2, 4))->format('Y-m-d'),
                'ended_at' => null,
            ]);
        }

        // Assign remaining staff to random facilities (1-3 facilities per staff)
        $assignedStaffIds = $doctorStaff->pluck('id')->merge($adminStaff->pluck('id'))->unique()->toArray();
        $facilityIds = $facilities->pluck('id')->toArray();
        $departmentIds = $departments->pluck('id')->toArray();

        Staff::whereNotIn('id', $assignedStaffIds)
            ->chunkById(200, function ($staffBatch) use ($facilityIds, $departmentIds, $positions, $roles) {
                $records = [];
                foreach ($staffBatch as $staff) {
                    $numFacilities = fake()->numberBetween(1, 3);
                    $assignedFacilities = [];
                    for ($i = 0; $i < $numFacilities; $i++) {
                        $facilityId = $facilityIds[array_rand($facilityIds)];
                        if (in_array($facilityId, $assignedFacilities)) {
                            continue;
                        }
                        $assignedFacilities[] = $facilityId;
                        $records[] = [
                            'uuid' => Str::uuid(),
                            'staff_id' => $staff->id,
                            'facility_id' => $facilityId,
                            'department_id' => $departmentIds[array_rand($departmentIds)],
                            'position_id' => ! empty($positions) ? $positions[array_rand($positions)] : null,
                            'role_id' => $roles->random()?->id,
                            'joined_at' => now()->subYears(fake()->numberBetween(0, 5))->format('Y-m-d'),
                            'ended_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                FacilityStaff::insert($records);
            });

        // Update department heads with facility staff records
        $departments->each(function (Department $department) {
            $fs = FacilityStaff::where('facility_id', $department->facility_id)->inRandomOrder()->first();
            if ($fs) {
                $department->update(['head_facility_staff_id' => $fs->id]);
            }
        });
    }
}
