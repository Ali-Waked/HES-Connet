<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Medicine;
use App\Models\PharmacyMedicine;
use Illuminate\Database\Seeder;

class PharmacyMedicineSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::all();
        $medicines = Medicine::all();

        if ($facilities->isEmpty() || $medicines->isEmpty()) {
            return;
        }

        $usedPairs = [];

        for ($i = 0; $i < 20; $i++) {
            $facility = $facilities->random();
            $medicine = $medicines->random();
            $pairKey = "{$facility->id}-{$medicine->id}";

            if (isset($usedPairs[$pairKey])) {
                continue;
            }

            $usedPairs[$pairKey] = true;

            PharmacyMedicine::create([
                'facility_id' => $facility->id,
                'medicine_id' => $medicine->id,
                'is_available' => fake()->boolean(80),
                'stock' => fake()->numberBetween(0, 500),
                'price' => fake()->randomFloat(2, 1, 999.99),
            ]);
        }
    }
}
