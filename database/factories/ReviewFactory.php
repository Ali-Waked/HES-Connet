<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Review;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'staff_id' => Staff::factory(),
            'patient_id' => Patient::factory(),
            'appointment_id' => Appointment::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'content' => fake()->randomElement([
                'Excellent doctor, very knowledgeable and caring.',
                'Great experience, the staff was very professional.',
                'Good service but had to wait a long time.',
                'Very satisfied with the treatment I received.',
                'The doctor listened carefully and explained everything.',
                'Average experience, room for improvement.',
                'Highly recommend this doctor to everyone.',
                'Professional and compassionate care.',
                'The best medical experience I have ever had.',
                'Kind staff and excellent medical advice.',
            ]),
        ];
    }
}
