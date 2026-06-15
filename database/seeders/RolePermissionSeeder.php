<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->createDefaultRoles();
        $this->assignPermissions();
    }

    private function createDefaultRoles(): void
    {
        $roles = [
            ['en' => 'super_admin', 'ar' => 'مشرف عام'],
            ['en' => 'organization_owner', 'ar' => 'مالك مؤسسة'],
            ['en' => 'facility_manager', 'ar' => 'مدير منشأة'],
            ['en' => 'department_manager', 'ar' => 'مدير قسم'],
            ['en' => 'doctor', 'ar' => 'طبيب'],
            ['en' => 'nurse', 'ar' => 'ممرض'],
            ['en' => 'pharmacist', 'ar' => 'صيدلي'],
            ['en' => 'patient', 'ar' => 'مريض'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name->en' => $role['en']],
                ['name' => $role,'uuid' => Str::uuid()]
            );
        }
    }

    private function assignPermissions(): void
    {
        $orgOwner = Role::where('name->en', 'organization_owner')->first();
        $facilityManager = Role::where('name->en', 'facility_manager')->first();
        $deptManager = Role::where('name->en', 'department_manager')->first();
        $doctor = Role::where('name->en', 'doctor')->first();
        $nurse = Role::where('name->en', 'nurse')->first();
        $patient = Role::where('name->en', 'patient')->first();

        $byKey = Permission::all()->keyBy('key');

        $orgOwner->permissions()->sync(
            collect([
                'organizations.view', 'organizations.manage', 'organizations.approve', 'organizations.reject',
                'facilities.view', 'facilities.manage', 'facilities.approve', 'facilities.reject',
                'departments.view', 'departments.manage',
                'staff.view', 'staff.manage',
                'patients.view', 'patients.manage',
                'facility_documents.view', 'facility_documents.manage', 'facility_documents.approve', 'facility_documents.reject',
                'facility_images.view', 'facility_images.manage',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $facilityManager->permissions()->sync(
            collect([
                'facilities.view', 'facilities.manage', 'facilities.approve', 'facilities.reject',
                'departments.view', 'departments.manage',
                'staff.view', 'staff.manage',
                'patients.view', 'patients.manage',
                'facility_documents.view', 'facility_documents.manage', 'facility_documents.approve', 'facility_documents.reject',
                'facility_images.view', 'facility_images.manage',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $deptManager->permissions()->sync(
            collect([
                'departments.view', 'departments.manage',
                'staff.view', 'staff.manage',
                'patients.view',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $doctor->permissions()->sync(
            collect([
                'patients.view', 'patients.manage',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $nurse->permissions()->sync(
            collect([
                'patients.view',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $patient->permissions()->sync(
            collect([
                'profile.view', 'profile.update',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );
    }
}
