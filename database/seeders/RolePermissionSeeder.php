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
            ['slug' => 'super_admin', 'en' => 'super_admin', 'ar' => 'مشرف عام', 'scope' => 'system'],
            ['slug' => 'organization_owner', 'en' => 'organization_owner', 'ar' => 'مالك مؤسسة', 'scope' => 'system'],
            ['slug' => 'facility_owner', 'en' => 'facility_owner', 'ar' => 'مالك المنشأة', 'scope' => 'facility'],
            ['slug' => 'facility_manager', 'en' => 'facility_manager', 'ar' => 'مدير منشأة', 'scope' => 'facility'],
            ['slug' => 'department_manager', 'en' => 'department_manager', 'ar' => 'مدير قسم', 'scope' => 'facility'],
            ['slug' => 'doctor', 'en' => 'doctor', 'ar' => 'طبيب', 'scope' => 'facility'],
            ['slug' => 'nurse', 'en' => 'nurse', 'ar' => 'ممرض', 'scope' => 'facility'],
            ['slug' => 'pharmacist', 'en' => 'pharmacist', 'ar' => 'صيدلي', 'scope' => 'facility'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => ['en' => $role['en'], 'ar' => $role['ar']],
                    'slug' => $role['slug'],
                    'scope' => $role['scope'],
                    'is_system' => $role['scope'] === 'system',
                    'uuid' => Str::uuid(),
                ]
            );
        }
    }

    private function assignPermissions(): void
    {
        $superAdmin = Role::where('slug', 'super_admin')->first();
        $orgOwner = Role::where('slug', 'organization_owner')->first();
        $facilityOwner = Role::where('slug', 'facility_owner')->first();
        $facilityManager = Role::where('slug', 'facility_manager')->first();
        $deptManager = Role::where('slug', 'department_manager')->first();
        $doctor = Role::where('slug', 'doctor')->first();
        $nurse = Role::where('slug', 'nurse')->first();
        $pharmacist = Role::where('slug', 'pharmacist')->first();
        $byKey = Permission::all()->keyBy('key');

        if ($superAdmin) {
            $superAdmin->permissions()->sync($byKey->pluck('id')->values()->toArray());
        }

        $orgOwner->permissions()->sync(
            collect([
                'dashboard.view',
                'organizations.view', 'organizations.manage', 'organizations.approve', 'organizations.reject',
                'facilities.view', 'facilities.manage', 'facilities.approve', 'facilities.reject',
                'departments.view', 'departments.manage',
                'staff.view', 'staff.manage',
                'patients.view', 'patients.manage',
                'facility_documents.view', 'facility_documents.manage', 'facility_documents.approve', 'facility_documents.reject',
                'facility_images.view', 'facility_images.manage',
                'reviews.view',
                'reports.view', 'reports.export',
                'analytics.view',
                'notifications.view', 'notifications.manage',
                'activity_logs.view',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        if ($facilityOwner) {
            $facilityOwner->permissions()->sync(
                collect([
                    'facility_dashboard.view',
                    'facilities.view', 'facilities.manage',
                    'departments.view', 'departments.manage',
                    'staff.view', 'staff.manage',
                    'staff_schedules.view', 'staff_schedules.manage',
                    'staff_unavailabilities.view', 'staff_unavailabilities.manage',
                    'patients.view', 'patients.manage',
                    'appointments.view', 'appointments.manage',
                    'prescriptions.view', 'prescriptions.manage',
                    'reviews.view', 'reviews.manage',
                    'reports.view', 'reports.export',
                    'analytics.view',
                    'notifications.view', 'notifications.manage',
                    'activity_logs.view',
                    'profile.view', 'profile.update',
                ])->map(fn (string $key) => $byKey->get($key)?->id)
                ->filter()
                ->values()
                ->toArray()
            );
        }

        $facilityManager->permissions()->sync(
            collect([
                'facility_dashboard.view',
                'facilities.view', 'facilities.manage',
                'departments.view', 'departments.manage',
                'staff.view', 'staff.manage',
                'patients.view', 'patients.manage',
                'facility_documents.view', 'facility_documents.manage', 'facility_documents.approve', 'facility_documents.reject',
                'facility_images.view', 'facility_images.manage',
                'reviews.view', 'reviews.manage',
                'reports.view', 'analytics.view',
                'notifications.view', 'notifications.manage',
                'activity_logs.view',
                'profile.view', 'profile.update',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $deptManager->permissions()->sync(
            collect([
                'facility_dashboard.view',
                'departments.view', 'departments.manage',
                'staff.view', 'staff.manage',
                'patients.view',
                'reviews.view',
                'notifications.view',
                'profile.view', 'profile.update',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $doctor->permissions()->sync(
            collect([
                'facility_dashboard.view',
                'patients.view', 'patients.manage',
                'appointments.view', 'appointments.manage',
                'prescriptions.view', 'prescriptions.manage',
                'reviews.view',
                'medication_requests.view', 'medication_requests.manage',
                'notifications.view',
                'profile.view', 'profile.update',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        $nurse->permissions()->sync(
            collect([
                'facility_dashboard.view',
                'patients.view',
                'appointments.view',
                'staff_schedules.view',
                'prescriptions.view',
                'reviews.view',
                'medication_requests.view',
                'notifications.view',
                'profile.view', 'profile.update',
            ])->map(fn (string $key) => $byKey->get($key)?->id)
            ->filter()
            ->values()
            ->toArray()
        );

        if ($pharmacist) {
            $pharmacist->permissions()->sync(
                collect([
                    'facility_dashboard.view',
                    'medicines.view',
                    'prescriptions.view', 'prescriptions.manage',
                    'medication_requests.view', 'medication_requests.manage', 'medication_requests.approve', 'medication_requests.reject',
                    'notifications.view',
                    'profile.view', 'profile.update',
                ])->map(fn (string $key) => $byKey->get($key)?->id)
                ->filter()
                ->values()
                ->toArray()
            );
        }

    }
}
