<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PrescriptionRoute;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'medicine_id' => Medicine::factory(),
            'dosage' => fake()->randomElement(['250mg', '500mg', '1g', '10mg', '5mg', '100mg', '50mg']),
            'frequency' => fake()->randomElement([
                'once daily',
                'twice daily',
                'three times daily',
                'every 4 hours',
                'every 6 hours',
                'as needed',
                'once weekly',
            ]),
            'duration' => fake()->randomElement(['3 days', '5 days', '7 days', '10 days', '14 days', '30 days', '90 days']),
            'route' => fake()->optional(0.8)->randomElement(PrescriptionRoute::cases()),
            'instructions' => fake()->optional(0.5)->sentence(),
            'quantity' => fake()->numberBetween(1, 6),
        ];
    }
}
