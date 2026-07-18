<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'super_admin', 'en' => 'Super Admin', 'ar' => 'مشرف عام', 'scope' => 'system'],
            ['slug' => 'organization_admin', 'en' => 'Organization Admin', 'ar' => 'مدير مؤسسة', 'scope' => 'system'],
            ['slug' => 'patient_portal_user', 'en' => 'Patient Portal User', 'ar' => 'مستخدم بوابة المريض', 'scope' => 'system'],
            ['slug' => 'facility_admin', 'en' => 'Facility Admin', 'ar' => 'مدير منشأة', 'scope' => 'facility'],
            ['slug' => 'department_manager', 'en' => 'Department Manager', 'ar' => 'مدير قسم', 'scope' => 'facility'],
            ['slug' => 'content_manager', 'en' => 'Content Manager', 'ar' => 'مدير محتوى', 'scope' => 'facility'],
            ['slug' => 'finance_manager', 'en' => 'Finance Manager', 'ar' => 'مدير مالي', 'scope' => 'facility'],
            ['slug' => 'doctor_portal_user', 'en' => 'Doctor Portal User', 'ar' => 'مستخدم بوابة الطبيب', 'scope' => 'facility'],
            ['slug' => 'pharmacy_portal_user', 'en' => 'Pharmacy Portal User', 'ar' => 'مستخدم بوابة الصيدلية', 'scope' => 'facility'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                [
                    'uuid' => Str::uuid(),
                    'name' => ['en' => $role['en'], 'ar' => $role['ar']],
                    'slug' => $role['slug'],
                    'scope' => $role['scope'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
