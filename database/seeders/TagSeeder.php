<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['en' => 'Diabetes', 'ar' => 'السكري'],
            ['en' => 'Hypertension', 'ar' => 'ارتفاع ضغط الدم'],
            ['en' => 'Vaccination', 'ar' => 'التطعيم'],
            ['en' => 'Pregnancy', 'ar' => 'الحمل'],
            ['en' => 'Mental Wellness', 'ar' => 'الصحة النفسية'],
            ['en' => 'First Aid', 'ar' => 'الإسعافات الأولية'],
            ['en' => 'Surgery', 'ar' => 'الجراحة'],
            ['en' => 'Pediatrics', 'ar' => 'طب الأطفال'],
            ['en' => 'Cardiology', 'ar' => 'أمراض القلب'],
            ['en' => 'Nutrition', 'ar' => 'التغذية'],
            ['en' => 'Cancer', 'ar' => 'السرطان'],
            ['en' => 'Pharmacy', 'ar' => 'الصيدلة'],
            ['en' => 'Emergency', 'ar' => 'الطوارئ'],
            ['en' => 'Elderly Care', 'ar' => 'رعاية المسنين'],
            ['en' => 'Public Health', 'ar' => 'الصحة العامة'],
            ['en' => 'Dental Health', 'ar' => 'صحة الأسنان'],
            ['en' => 'Ophthalmology', 'ar' => 'طب العيون'],
            ['en' => 'Dermatology', 'ar' => 'الأمراض الجلدية'],
            ['en' => 'Orthopedics', 'ar' => 'جراحة العظام'],
            ['en' => 'Physiotherapy', 'ar' => 'العلاج الطبيعي'],
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'uuid' => Str::uuid(),
                'name' => ['en' => $tag['en'], 'ar' => $tag['ar']],
            ]);
        }
    }
}
