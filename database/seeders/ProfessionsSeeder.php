<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Profession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfessionsSeeder extends Seeder
{
    public function run(): void
    {
        $professions = [
            ['slug' => 'doctor', 'en' => 'Doctor', 'ar' => 'طبيب'],
            ['slug' => 'pharmacist', 'en' => 'Pharmacist', 'ar' => 'صيدلي'],
            ['slug' => 'nurse', 'en' => 'Nurse', 'ar' => 'ممرض'],
            ['slug' => 'lab_technician', 'en' => 'Lab Technician', 'ar' => 'فني مختبر'],
            ['slug' => 'radiologist', 'en' => 'Radiologist', 'ar' => 'أشعة'],
            ['slug' => 'dentist', 'en' => 'Dentist', 'ar' => 'طبيب أسنان'],
            ['slug' => 'receptionist', 'en' => 'Receptionist', 'ar' => 'موظف استقبال'],
            ['slug' => 'accountant', 'en' => 'Accountant', 'ar' => 'محاسب'],
            ['slug' => 'driver', 'en' => 'Driver', 'ar' => 'سائق'],
        ];

        foreach ($professions as $profession) {
            Profession::firstOrCreate(
                ['slug' => $profession['slug']],
                [
                    'uuid' => Str::uuid(),
                    'name' => ['en' => $profession['en'], 'ar' => $profession['ar']],
                    'slug' => $profession['slug'],
                    'is_active' => true,
                ]
            );
        }
    }
}
