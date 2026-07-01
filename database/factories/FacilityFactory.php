<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Facility;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        $types = ['hospital', 'clinic', 'pharmacy', 'medical_point'];
        $statuses = ['pending', 'active', 'inactive', 'temporarily_closed', 'permanently_closed'];
        $approvalStatuses = ['pending', 'approved', 'rejected', 'suspended'];

        return [
            'uuid' => Str::uuid(),
            'name' => [
                'en' => fake()->company().' '.fake()->randomElement(['Medical Center', 'Hospital', 'Clinic', 'Health Center']),
                'ar' => fake('ar_SA')->company().' '.fake()->randomElement(['الطبي', 'الصحي', 'للاستشارات']),
            ],
            'description' => [
                'en' => fake()->paragraph(),
                'ar' => fake('ar_SA')->paragraph(),
            ],
            'latitude' => fake()->latitude(31.2, 31.6),
            'longitude' => fake()->longitude(34.2, 34.6),
            'facility_type' => fake()->randomElement($types),
            'status' => fake()->randomElement($statuses),
            'approval_status' => fake()->randomElement($approvalStatuses),
            'cover_image' => null,
            'organization_id' => Organization::factory(),
            'owner_id' => User::factory(),
            'created_by' => User::factory(),
            'city_id' => City::factory(),
            'is_featured' => fake()->boolean(20),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'status' => 'active',
        ]);
    }

    public function hospital(): static
    {
        return $this->state(fn (array $attributes) => [
            'facility_type' => 'hospital',
        ]);
    }

    public function clinic(): static
    {
        return $this->state(fn (array $attributes) => [
            'facility_type' => 'clinic',
        ]);
    }

    public function pharmacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'facility_type' => 'pharmacy',
        ]);
    }
}
