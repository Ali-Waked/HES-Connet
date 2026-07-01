<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Symptom;
use Illuminate\Database\Eloquent\Factories\Factory;

class SymptomFactory extends Factory
{
    protected $model = Symptom::class;

    public function definition(): array
    {
        $symptoms = [
            'Fever', 'Cough', 'Headache', 'Fatigue', 'Nausea',
            'Dizziness', 'Chest Pain', 'Shortness of Breath', 'Sore Throat',
            'Muscle Ache', 'Back Pain', 'Abdominal Pain', 'Diarrhea',
            'Constipation', 'Skin Rash', 'Joint Pain', 'Loss of Appetite',
            'Blurred Vision', 'Ear Pain', 'Numbness',
        ];

        $symptom = fake()->randomElement($symptoms);

        return [
            'name' => [
                'en' => $symptom,
                'ar' => fake('ar_SA')->word(),
            ],
            'is_active' => true,
        ];
    }
}
