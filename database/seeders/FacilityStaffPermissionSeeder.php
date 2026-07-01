<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FacilityStaff;
use App\Models\FacilityStaffPermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class FacilityStaffPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $staff = FacilityStaff::all();
        $permissions = Permission::all();

        if ($staff->isEmpty() || $permissions->isEmpty()) {
            return;
        }

        $usedPairs = [];

        for ($i = 0; $i < 20; $i++) {
            $staffMember = $staff->random();
            $permission = $permissions->random();
            $pairKey = "{$staffMember->id}-{$permission->id}";

            if (isset($usedPairs[$pairKey])) {
                continue;
            }

            $usedPairs[$pairKey] = true;

            FacilityStaffPermission::create([
                'facility_staff_id' => $staffMember->id,
                'permission_id' => $permission->id,
                'enabled' => fake()->boolean(85),
            ]);
        }
    }
}
