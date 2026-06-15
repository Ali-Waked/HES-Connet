<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            [
                'uuid' => Str::uuid(),
                'name' => ['en' => 'Gaza', 'ar' => 'غزة'],
                'is_active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => ['en' => 'North Gaza', 'ar' => 'شمال غزة'],
                'is_active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => ['en' => 'Deir al-Balah', 'ar' => 'دير البلح'],
                'is_active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => ['en' => 'Khan Younis', 'ar' => 'خان يونس'],
                'is_active' => true,
            ],
            [
                'uuid' => Str::uuid(),
                'name' => ['en' => 'Rafah', 'ar' => 'رفح'],
                'is_active' => true,
            ],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
