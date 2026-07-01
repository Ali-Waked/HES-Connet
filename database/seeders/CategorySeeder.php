<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Article categories
            ['name' => ['en' => 'General Health', 'ar' => 'الصحة العامة'], 'type' => 'article'],
            ['name' => ['en' => 'Nutrition', 'ar' => 'التغذية'], 'type' => 'article'],
            ['name' => ['en' => 'Mental Health', 'ar' => 'الصحة النفسية'], 'type' => 'article'],
            ['name' => ['en' => 'Heart Health', 'ar' => 'صحة القلب'], 'type' => 'article'],
            ['name' => ['en' => 'Pediatrics', 'ar' => 'طب الأطفال'], 'type' => 'article'],
            ['name' => ['en' => 'Women\'s Health', 'ar' => 'صحة المرأة'], 'type' => 'article'],
            ['name' => ['en' => 'Preventive Medicine', 'ar' => 'الطب الوقائي'], 'type' => 'article'],
            ['name' => ['en' => 'Pharmacy & Medications', 'ar' => 'الصيدلة والأدوية'], 'type' => 'article'],

            // Story categories
            ['name' => ['en' => 'Medical Treatment', 'ar' => 'علاج طبي'], 'type' => 'story'],
            ['name' => ['en' => 'Emergency Relief', 'ar' => 'إغاثة طارئة'], 'type' => 'story'],
            ['name' => ['en' => 'Child Healthcare', 'ar' => 'رعاية الأطفال'], 'type' => 'story'],
            ['name' => ['en' => 'Community Support', 'ar' => 'دعم مجتمعي'], 'type' => 'story'],
            ['name' => ['en' => 'Surgery & Recovery', 'ar' => 'جراحة وتعافي'], 'type' => 'story'],

            // Job categories
            ['name' => ['en' => 'Medical Doctors', 'ar' => 'أطباء'], 'type' => 'job'],
            ['name' => ['en' => 'Nursing', 'ar' => 'تمريض'], 'type' => 'job'],
            ['name' => ['en' => 'Pharmacy', 'ar' => 'صيدلة'], 'type' => 'job'],
            ['name' => ['en' => 'Administration', 'ar' => 'إدارة'], 'type' => 'job'],
            ['name' => ['en' => 'Technology', 'ar' => 'تكنولوجيا'], 'type' => 'job'],
            ['name' => ['en' => 'Allied Health', 'ar' => 'المهن الصحية المساعدة'], 'type' => 'job'],
            ['name' => ['en' => 'Public Health', 'ar' => 'الصحة العامة'], 'type' => 'article'],
        ];

        foreach ($categories as $data) {
            Category::create([
                'uuid' => Str::uuid(),
                'name' => $data['name'],
                'type' => $data['type'],
                'is_active' => true,
            ]);
        }
    }
}
