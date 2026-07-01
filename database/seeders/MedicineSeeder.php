<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            ['en' => 'Paracetamol 500mg', 'ar' => 'باراسيتامول 500 ملغ'],
            ['en' => 'Ibuprofen 400mg', 'ar' => 'إيبوبروفين 400 ملغ'],
            ['en' => 'Amoxicillin 500mg', 'ar' => 'أموكسيسيلين 500 ملغ'],
            ['en' => 'Omeprazole 20mg', 'ar' => 'أوميبرازول 20 ملغ'],
            ['en' => 'Metformin 850mg', 'ar' => 'ميتفورمين 850 ملغ'],
            ['en' => 'Atorvastatin 10mg', 'ar' => 'أتورفاستاتين 10 ملغ'],
            ['en' => 'Lisinopril 10mg', 'ar' => 'ليزينوبريل 10 ملغ'],
            ['en' => 'Losartan 50mg', 'ar' => 'لوسارتان 50 ملغ'],
            ['en' => 'Aspirin 100mg', 'ar' => 'أسبرين 100 ملغ'],
            ['en' => 'Cetirizine 10mg', 'ar' => 'سيتريزين 10 ملغ'],
            ['en' => 'Salbutamol Inhaler', 'ar' => 'جهاز استنشاق سالبيوتامول'],
            ['en' => 'Prednisolone 5mg', 'ar' => 'بريدنيزولون 5 ملغ'],
            ['en' => 'Diazepam 5mg', 'ar' => 'ديازيبام 5 ملغ'],
            ['en' => 'Fluoxetine 20mg', 'ar' => 'فلوكسيتين 20 ملغ'],
            ['en' => 'Warfarin 5mg', 'ar' => 'وارفارين 5 ملغ'],
            ['en' => 'Insulin Glargine', 'ar' => 'أنسولين غلارجين'],
            ['en' => 'Levothyroxine 50mcg', 'ar' => 'ليفوثيروكسين 50 مكغ'],
            ['en' => 'Azithromycin 250mg', 'ar' => 'أزيثروميسين 250 ملغ'],
            ['en' => 'Ciprofloxacin 500mg', 'ar' => 'سيبروفلوكساسين 500 ملغ'],
            ['en' => 'Vitamin D3 1000IU', 'ar' => 'فيتامين د3 1000 وحدة دولية'],
        ];

        foreach ($medicines as $medicine) {
            Medicine::create([
                'uuid' => Str::uuid(),
                'name' => ['en' => $medicine['en'], 'ar' => $medicine['ar']],
                'description' => [
                    'en' => 'Standard pharmaceutical preparation',
                    'ar' => 'مستحضر صيدلاني قياسي',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400',
            ]);
        }
    }
}
