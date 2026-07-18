<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $roles = DB::table('roles');

        // 1. Create facility_admin role if it doesn't exist
        $facilityAdmin = $roles->where('slug', 'facility_admin')->first();

        if (! $facilityAdmin) {
            $facilityAdminId = DB::table('roles')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => json_encode(['en' => 'Facility Admin', 'ar' => 'مدير منشأة']),
                'slug' => 'facility_admin',
                'scope' => 'facility',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $facilityAdminId = $facilityAdmin->id;

            // Update name translations to the unified label
            DB::table('roles')
                ->where('id', $facilityAdminId)
                ->update(['name' => json_encode(['en' => 'Facility Admin', 'ar' => 'مدير منشأة'])]);
        }

        // 2. Find old role IDs
        $oldSlugs = ['hospital_admin', 'clinic_admin'];
        $oldRoleIds = $roles->whereIn('slug', $oldSlugs)->pluck('id')->toArray();

        if (empty($oldRoleIds)) {
            return;
        }

        // 3. Migrate user_role assignments: re-point old roles to facility_admin
        DB::table('user_role')
            ->whereIn('role_id', $oldRoleIds)
            ->whereNotIn('role_id', [$facilityAdminId])
            ->update(['role_id' => $facilityAdminId]);

        // 4. Migrate facility_staff role assignments: re-point old roles to facility_admin
        DB::table('facility_staff')
            ->whereIn('role_id', $oldRoleIds)
            ->whereNot('role_id', $facilityAdminId)
            ->update(['role_id' => $facilityAdminId]);

        // 5. Migrate role_permission entries: sync facility_admin with the union of old permissions
        $oldPermissionIds = DB::table('role_permission')
            ->whereIn('role_id', $oldRoleIds)
            ->pluck('permission_id')
            ->unique()
            ->toArray();

        if (! empty($oldPermissionIds)) {
            $existingPermissionIds = DB::table('role_permission')
                ->where('role_id', $facilityAdminId)
                ->pluck('permission_id')
                ->toArray();

            $newPermissionIds = array_unique(array_merge($existingPermissionIds, $oldPermissionIds));

            $toInsert = array_diff($newPermissionIds, $existingPermissionIds);
            if (! empty($toInsert)) {
                DB::table('role_permission')->insert(
                    array_map(fn ($permId) => [
                        'role_id' => $facilityAdminId,
                        'permission_id' => $permId,
                    ], $toInsert)
                );
            }
        }

        // 6. Delete old role_permission entries
        DB::table('role_permission')
            ->whereIn('role_id', $oldRoleIds)
            ->delete();

        // 7. Delete old roles
        $roles->whereIn('slug', $oldSlugs)->delete();
    }

    public function down(): void
    {
        // Reverse: re-create hospital_admin and clinic_admin roles
        $roles = DB::table('roles');

        $hospitalId = $roles->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => json_encode(['en' => 'Hospital Admin', 'ar' => 'مدير مستشفى']),
            'slug' => 'hospital_admin',
            'scope' => 'facility',
            'is_system' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clinicId = $roles->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => json_encode(['en' => 'Clinic Admin', 'ar' => 'مدير عيادة']),
            'slug' => 'clinic_admin',
            'scope' => 'facility',
            'is_system' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Copy permissions from facility_admin to both old roles
        $facilityAdmin = $roles->where('slug', 'facility_admin')->first();
        if ($facilityAdmin) {
            $permissionIds = DB::table('role_permission')
                ->where('role_id', $facilityAdmin->id)
                ->pluck('permission_id')
                ->toArray();

            if (! empty($permissionIds)) {
                DB::table('role_permission')->insert(
                    array_merge(
                        array_map(fn ($id) => ['role_id' => $hospitalId, 'permission_id' => $id], $permissionIds),
                        array_map(fn ($id) => ['role_id' => $clinicId, 'permission_id' => $id], $permissionIds),
                    )
                );
            }

            // Remove facility_admin role
            DB::table('role_permission')->where('role_id', $facilityAdmin->id)->delete();
            $roles->where('id', $facilityAdmin->id)->delete();
        }
    }
};
