<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Facility;
use App\Models\Medicine;
use App\Models\PharmacyMedicine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PharmacyMedicineFactory extends Factory
{
    protected $model = PharmacyMedicine::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'facility_id' => Facility::factory(),
            'medicine_id' => Medicine::factory(),
            'is_available' => fake()->boolean(80),
            'stock' => fake()->numberBetween(0, 500),
            'price' => fake()->randomFloat(2, 1, 200),
        ];
    }
}
