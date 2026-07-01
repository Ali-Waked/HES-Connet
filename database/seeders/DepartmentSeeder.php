<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => ['en' => 'Cardiology', 'ar' => 'أمراض القلب'], 'image' => 'https://images.unsplash.com/photo-1628595351029-c2bf17511435?w=400'],
            ['name' => ['en' => 'Pediatrics', 'ar' => 'طب الأطفال'], 'image' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=400'],
            ['name' => ['en' => 'Neurology', 'ar' => 'الأعصاب'], 'image' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=400'],
            ['name' => ['en' => 'Orthopedics', 'ar' => 'جراحة العظام'], 'image' => 'https://images.unsplash.com/photo-1579165466741-7f35e4755661?w=400'],
            ['name' => ['en' => 'Dermatology', 'ar' => 'الأمراض الجلدية'], 'image' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=400'],
            ['name' => ['en' => 'Ophthalmology', 'ar' => 'طب العيون'], 'image' => 'https://images.unsplash.com/photo-1579684453423-f84349ef60b0?w=400'],
            ['name' => ['en' => 'ENT', 'ar' => 'أنف وأذن وحنجرة'], 'image' => 'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?w=400'],
            ['name' => ['en' => 'General Surgery', 'ar' => 'جراحة عامة'], 'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?w=400'],
            ['name' => ['en' => 'Internal Medicine', 'ar' => 'الباطنة'], 'image' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=400'],
            ['name' => ['en' => 'Emergency Medicine', 'ar' => 'طب الطوارئ'], 'image' => 'https://images.unsplash.com/photo-1587745416684-47953f16fdd1?w=400'],
            ['name' => ['en' => 'Radiology', 'ar' => 'الأشعة'], 'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?w=400'],
            ['name' => ['en' => 'Pathology', 'ar' => 'علم الأمراض'], 'image' => 'https://images.unsplash.com/photo-1579165466741-7f35e4755661?w=400'],
            ['name' => ['en' => 'Anesthesiology', 'ar' => 'التخدير'], 'image' => 'https://images.unsplash.com/photo-1628595351029-c2bf17511435?w=400'],
            ['name' => ['en' => 'Obstetrics & Gynecology', 'ar' => 'النساء والتوليد'], 'image' => 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=400'],
            ['name' => ['en' => 'Urology', 'ar' => 'المسالك البولية'], 'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?w=400'],
            ['name' => ['en' => 'Neonatology', 'ar' => 'حديثي الولادة'], 'image' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=400'],
            ['name' => ['en' => 'Oncology', 'ar' => 'الأورام'], 'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?w=400'],
            ['name' => ['en' => 'Nephrology', 'ar' => 'أمراض الكلى'], 'image' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=400'],
            ['name' => ['en' => 'Gastroenterology', 'ar' => 'أمراض الجهاز الهضمي'], 'image' => 'https://images.unsplash.com/photo-1579165466741-7f35e4755661?w=400'],
            ['name' => ['en' => 'Psychiatry', 'ar' => 'الطب النفسي'], 'image' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=400'],
        ];

        foreach ($departments as $data) {
            Department::create([
                'uuid' => Str::uuid(),
                'name' => $data['name'],
                'description' => [
                    'en' => 'Specialized department providing comprehensive medical care.',
                    'ar' => 'قسم متخصص يقدم رعاية طبية شاملة.',
                ],
                'image' => $data['image'],
                'head_facility_staff_id' => null,
                'is_active' => true,
            ]);
        }
    }
}
