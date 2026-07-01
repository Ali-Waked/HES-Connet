<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->randomElement([
                    'Cardiology', 'Pediatrics', 'Neurology', 'Orthopedics',
                    'Dermatology', 'Ophthalmology', 'ENT', 'General Surgery',
                    'Internal Medicine', 'Emergency', 'Radiology', 'Pathology',
                    'Anesthesiology', 'Obstetrics & Gynecology', 'Urology',
                    'Neonatology', 'Oncology', 'Nephrology', 'Gastroenterology', 'Psychiatry',
                ]),
                'ar' => fake('ar_SA')->randomElement([
                    'قلب', 'أطفال', 'أعصاب', 'عظام', 'جلدية', 'عيون',
                    'أنف وأذن وحنجرة', 'جراحة عامة', 'باطنة', 'طوارئ',
                    'أشعة', 'تشريح', 'تخدير', 'نساء وتوليد', 'مسالك بولية',
                    'حديثي الولادة', 'أورام', 'كلى', 'جهاز هضمي', 'نفسي',
                ]),
            ],
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
            'image' => null,
            'head_facility_staff_id' => null,
            'is_active' => true,
        ];
    }
}
