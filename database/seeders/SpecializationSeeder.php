<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            ['slug' => 'general_medicine', 'en' => 'General Medicine', 'ar' => 'طب عام'],
            ['slug' => 'cardiology', 'en' => 'Cardiology', 'ar' => 'أمراض القلب'],
            ['slug' => 'pediatrics', 'en' => 'Pediatrics', 'ar' => 'طب الأطفال'],
            ['slug' => 'neurology', 'en' => 'Neurology', 'ar' => 'الأعصاب'],
            ['slug' => 'dermatology', 'en' => 'Dermatology', 'ar' => 'الأمراض الجلدية'],
            ['slug' => 'orthopedics', 'en' => 'Orthopedics', 'ar' => 'جراحة العظام'],
            ['slug' => 'ophthalmology', 'en' => 'Ophthalmology', 'ar' => 'طب العيون'],
            ['slug' => 'ent', 'en' => 'Ear, Nose and Throat', 'ar' => 'أنف وأذن وحنجرة'],
            ['slug' => 'general_surgery', 'en' => 'General Surgery', 'ar' => 'جراحة عامة'],
            ['slug' => 'internal_medicine', 'en' => 'Internal Medicine', 'ar' => 'الباطنة'],
            ['slug' => 'emergency_medicine', 'en' => 'Emergency Medicine', 'ar' => 'طب الطوارئ'],
            ['slug' => 'family_medicine', 'en' => 'Family Medicine', 'ar' => 'طب العائلة'],
            ['slug' => 'obgyn', 'en' => 'Obstetrics and Gynecology', 'ar' => 'النساء والتوليد'],
            ['slug' => 'urology', 'en' => 'Urology', 'ar' => 'المسالك البولية'],
            ['slug' => 'psychiatry', 'en' => 'Psychiatry', 'ar' => 'الطب النفسي'],
            ['slug' => 'radiology', 'en' => 'Radiology', 'ar' => 'الأشعة'],
            ['slug' => 'anesthesiology', 'en' => 'Anesthesiology', 'ar' => 'التخدير'],
            ['slug' => 'pathology', 'en' => 'Pathology', 'ar' => 'علم الأمراض'],
            ['slug' => 'oncology', 'en' => 'Oncology', 'ar' => 'الأورام'],
            ['slug' => 'nephrology', 'en' => 'Nephrology', 'ar' => 'أمراض الكلى'],
            ['slug' => 'gastroenterology', 'en' => 'Gastroenterology', 'ar' => 'أمراض الجهاز الهضمي'],
            ['slug' => 'pulmonology', 'en' => 'Pulmonology', 'ar' => 'أمراض الصدر'],
            ['slug' => 'endocrinology', 'en' => 'Endocrinology', 'ar' => 'الغدد الصماء'],
            ['slug' => 'rheumatology', 'en' => 'Rheumatology', 'ar' => 'الروماتيزم'],
            ['slug' => 'hematology', 'en' => 'Hematology', 'ar' => 'أمراض الدم'],
            ['slug' => 'infectious_disease', 'en' => 'Infectious Diseases', 'ar' => 'الأمراض المعدية'],
            ['slug' => 'neonatology', 'en' => 'Neonatology', 'ar' => 'حديثي الولادة'],
            ['slug' => 'neurosurgery', 'en' => 'Neurosurgery', 'ar' => 'جراحة الأعصاب'],
            ['slug' => 'plastic_surgery', 'en' => 'Plastic Surgery', 'ar' => 'الجراحة التجميلية'],
            ['slug' => 'pediatric_surgery', 'en' => 'Pediatric Surgery', 'ar' => 'جراحة الأطفال'],
            ['slug' => 'vascular_surgery', 'en' => 'Vascular Surgery', 'ar' => 'جراحة الأوعية الدموية'],
            ['slug' => 'sports_medicine', 'en' => 'Sports Medicine', 'ar' => 'الطب الرياضي'],
            ['slug' => 'geriatrics', 'en' => 'Geriatrics', 'ar' => 'طب المسنين'],
            ['slug' => 'allergy_immunology', 'en' => 'Allergy and Immunology', 'ar' => 'الحساسية والمناعة'],
            ['slug' => 'clinical_pharmacy', 'en' => 'Clinical Pharmacy', 'ar' => 'الصيدلة السريرية'],
        ];

        foreach ($specializations as $spec) {
            Specialization::firstOrCreate(
                ['name->en' => $spec['en']],
                [
                    'uuid' => Str::uuid(),
                    'name' => ['en' => $spec['en'], 'ar' => $spec['ar']],
                ]
            );
        }
    }
}
