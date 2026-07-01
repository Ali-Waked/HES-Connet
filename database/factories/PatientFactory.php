<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'medical_history' => fake()->randomElement([
                null,
                'Hypertension',
                'Diabetes Type 2',
                'Asthma',
                'No significant history',
                'Allergic to penicillin',
                'Hypothyroidism',
                'Previous surgery (appendectomy)',
            ]),
            'status' => AccountStatus::ACTIVE,
        ];
    }
}
