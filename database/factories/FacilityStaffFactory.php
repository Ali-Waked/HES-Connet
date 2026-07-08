<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Facility;
use App\Models\FacilityStaff;
use App\Models\Role;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FacilityStaffFactory extends Factory
{
    protected $model = FacilityStaff::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'staff_id' => Staff::factory(),
            'facility_id' => Facility::factory(),
            'department_id' => null,
            'position_id' => null,
            'role_id' => Role::factory(),
            'joined_at' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'ended_at' => null,
        ];
    }

    public function withRole(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('slug', $slug)->first()?->id ?? Role::factory(),
        ]);
    }
}
