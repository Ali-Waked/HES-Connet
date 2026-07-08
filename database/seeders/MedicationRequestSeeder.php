<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MedicationRequest;
use Illuminate\Database\Seeder;

class MedicationRequestSeeder extends Seeder
{
    public function run(): void
    {
        MedicationRequest::factory()
            ->count(15)
            ->pending()
            ->create();

        MedicationRequest::factory()
            ->count(10)
            ->approved()
            ->create();

        MedicationRequest::factory()
            ->count(8)
            ->dispensed()
            ->create();

        MedicationRequest::factory()
            ->count(7)
            ->create();
    }
}
