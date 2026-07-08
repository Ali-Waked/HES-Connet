<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Prescription;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    public function run(): void
    {
        Prescription::factory()
            ->count(30)
            ->active()
            ->withItems(3)
            ->create();

        Prescription::factory()
            ->count(10)
            ->active()
            ->withItems(5)
            ->create();

        Prescription::factory()
            ->count(10)
            ->withItems(2)
            ->create();
    }
}
