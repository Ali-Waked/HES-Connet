<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->randomElement([
                    'Paracetamol', 'Ibuprofen', 'Amoxicillin', 'Omeprazole',
                    'Metformin', 'Atorvastatin', 'Lisinopril', 'Losartan',
                    'Aspirin', 'Cetirizine', 'Salbutamol', 'Prednisolone',
                    'Diazepam', 'Fluoxetine', 'Warfarin', 'Insulin Glargine',
                    'Levothyroxine', 'Pantoprazole', 'Azithromycin', 'Ciprofloxacin',
                ]),
                'ar' => fake('ar_SA')->word(),
            ],
            'description' => [
                'en' => fake()->sentence(),
                'ar' => fake('ar_SA')->sentence(),
            ],
            'image_url' => null,
        ];
    }
}
