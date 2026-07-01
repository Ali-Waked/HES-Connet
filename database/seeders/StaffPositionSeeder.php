<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
use App\Models\StaffPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StaffPositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            [
                'name' => ['en' => 'Medical Director', 'ar' => 'المدير الطبي'],
                'description' => ['en' => 'Oversees all medical operations', 'ar' => 'يشرف على جميع العمليات الطبية'],
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Head of Department', 'ar' => 'رئيس القسم'],
                'description' => ['en' => 'Leads a specific department', 'ar' => 'يقود قسمًا معينًا'],
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Senior Consultant', 'ar' => 'استشاري أول'],
                'description' => ['en' => 'Senior medical consultant', 'ar' => 'استشاري طبي أول'],
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Specialist', 'ar' => 'أخصائي'],
                'description' => ['en' => 'Medical specialist', 'ar' => 'أخصائي طبي'],
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'General Practitioner', 'ar' => 'طبيب عام'],
                'description' => ['en' => 'General medical practitioner', 'ar' => 'طبيب ممارس عام'],
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Resident', 'ar' => 'مقيم'],
                'description' => ['en' => 'Medical resident in training', 'ar' => 'مقيم طبي قيد التدريب'],
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }

        // Also create StaffPosition records for the staff_positions table
        $staffPositions = [
            ['name' => ['en' => 'Medical Director', 'ar' => 'المدير الطبي'], 'description' => ['en' => 'Oversees all medical operations', 'ar' => 'يشرف على جميع العمليات الطبية']],
            ['name' => ['en' => 'Head of Department', 'ar' => 'رئيس القسم'], 'description' => ['en' => 'Leads a specific department', 'ar' => 'يقود قسمًا معينًا']],
            ['name' => ['en' => 'Senior Consultant', 'ar' => 'استشاري أول'], 'description' => ['en' => 'Senior medical consultant', 'ar' => 'استشاري طبي أول']],
            ['name' => ['en' => 'Specialist', 'ar' => 'أخصائي'], 'description' => ['en' => 'Medical specialist', 'ar' => 'أخصائي طبي']],
            ['name' => ['en' => 'General Practitioner', 'ar' => 'طبيب عام'], 'description' => ['en' => 'General medical practitioner', 'ar' => 'طبيب ممارس عام']],
            ['name' => ['en' => 'Resident', 'ar' => 'مقيم'], 'description' => ['en' => 'Medical resident in training', 'ar' => 'مقيم طبي قيد التدريب']],
        ];

        foreach ($staffPositions as $sp) {
            StaffPosition::create([
                'uuid' => Str::uuid(),
                'name' => $sp['name'],
                'description' => $sp['description'],
                'is_active' => true,
            ]);
        }
    }
}
