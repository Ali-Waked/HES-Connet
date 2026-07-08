<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MedicationRequestStatus;
use App\Models\Facility;
use App\Models\MedicationRequest;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MedicationRequestFactory extends Factory
{
    protected $model = MedicationRequest::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'patient_id' => Patient::factory(),
            'facility_id' => Facility::factory(),
            'prescription_id' => Prescription::factory(),
            'status' => fake()->randomElement(MedicationRequestStatus::cases()),
            'notes' => fake()->optional(0.4)->sentence(),
            'dispensed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MedicationRequestStatus::PENDING,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MedicationRequestStatus::APPROVED,
        ]);
    }

    public function dispensed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MedicationRequestStatus::APPROVED,
            'dispensed_at' => now(),
        ]);
    }
}
