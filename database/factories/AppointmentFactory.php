<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\FacilityStaff;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('-1 month', '+1 month');
        $endAt = (clone $startAt)->modify('+30 minutes');

        return [
            'uuid' => Str::uuid(),
            'facility_staff_id' => fn () => FacilityStaff::inRandomOrder()->first()?->id ?? FacilityStaff::factory(),
            'patient_id' => Patient::factory(),
            'start_at' => $startAt->format('Y-m-d H:i:s'),
            'end_at' => $endAt->format('Y-m-d H:i:s'),
            'status' => fake()->randomElement(AppointmentStatus::cases()),
            'reason' => fake()->randomElement([
                'Regular checkup', 'Follow-up visit', 'Consultation',
                'Blood test', 'Vaccination', 'Persistent cough',
                'Headache', 'Skin rash', 'Abdominal pain',
                'Annual physical examination',
            ]),
            'notes' => fake()->optional(0.3)->sentence(),
            'cancellation_reason' => null,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AppointmentStatus::SCHEDULED,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AppointmentStatus::COMPLETED,
        ]);
    }
}
