<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patientEmails = [
            'fatima@example.com', 'omar@example.com', 'nadia@example.com',
            'khaled@example.com', 'tariq@example.com', 'reem@example.com',
        ];

        $users = User::whereIn('email', $patientEmails)->get();

        foreach ($users as $user) {
            Patient::create([
                'user_id' => $user->id,
                'medical_history' => fake()->randomElement([
                    null, 'Hypertension', 'Diabetes Type 2', 'Asthma',
                    'No significant history', 'Allergic to penicillin',
                ]),
                'status' => AccountStatus::ACTIVE,
            ]);
        }

        // Create additional patients from remaining users
        $existingPatientUserIds = $users->pluck('id')->toArray();
        $remainingUsers = User::whereNotIn('id', $existingPatientUserIds)
            ->where('email', '!=', 'admin@gmail.com')
            ->take(10)
            ->get();

        foreach ($remainingUsers as $user) {
            Patient::create([
                'user_id' => $user->id,
                'medical_history' => fake()->optional(0.4)->randomElement([
                    'Hypothyroidism', 'Seasonal allergies', 'Migraine',
                    'High cholesterol', 'GERD',
                ]),
                'status' => AccountStatus::ACTIVE,
            ]);
        }

        // Create additional patients with new users
        Patient::factory()
            ->count(10)
            ->create();
    }
}
