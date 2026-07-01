<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    public function run(): void
    {
        $symptoms = [
            ['en' => 'Fever', 'ar' => 'حمى'],
            ['en' => 'Cough', 'ar' => 'سعال'],
            ['en' => 'Headache', 'ar' => 'صداع'],
            ['en' => 'Fatigue', 'ar' => 'إرهاق'],
            ['en' => 'Nausea', 'ar' => 'غثيان'],
            ['en' => 'Dizziness', 'ar' => 'دوخة'],
            ['en' => 'Chest Pain', 'ar' => 'ألم في الصدر'],
            ['en' => 'Shortness of Breath', 'ar' => 'ضيق في التنفس'],
            ['en' => 'Sore Throat', 'ar' => 'التهاب الحلق'],
            ['en' => 'Muscle Ache', 'ar' => 'آلام العضلات'],
            ['en' => 'Back Pain', 'ar' => 'آلام الظهر'],
            ['en' => 'Abdominal Pain', 'ar' => 'ألم في البطن'],
            ['en' => 'Diarrhea', 'ar' => 'إسهال'],
            ['en' => 'Constipation', 'ar' => 'إمساك'],
            ['en' => 'Skin Rash', 'ar' => 'طفح جلدي'],
            ['en' => 'Joint Pain', 'ar' => 'آلام المفاصل'],
            ['en' => 'Loss of Appetite', 'ar' => 'فقدان الشهية'],
            ['en' => 'Blurred Vision', 'ar' => 'تشوش الرؤية'],
            ['en' => 'Ear Pain', 'ar' => 'ألم الأذن'],
            ['en' => 'Numbness', 'ar' => 'خدران'],
        ];

        foreach ($symptoms as $symptom) {
            Symptom::create([
                'name' => ['en' => $symptom['en'], 'ar' => $symptom['ar']],
                'is_active' => true,
            ]);
        }
    }
}
