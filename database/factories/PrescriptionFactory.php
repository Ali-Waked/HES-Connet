<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PrescriptionStatus;
use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'appointment_id' => fn () => Appointment::inRandomOrder()->first()?->id ?? Appointment::factory(),
            'notes' => fake()->optional(0.7)->sentence(),
            'status' => fake()->randomElement(PrescriptionStatus::cases()),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PrescriptionStatus::ACTIVE,
        ]);
    }

    public function withItems(int $count = 3): static
    {
        return $this->has(PrescriptionItemFactory::new()->count($count), 'items');
    }
}
