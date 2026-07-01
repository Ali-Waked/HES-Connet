<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Facility;
use App\Models\FacilityReview;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityReviewFactory extends Factory
{
    protected $model = FacilityReview::class;

    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'patient_id' => Patient::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->randomElement([
                'Well-equipped facility with friendly staff.',
                'Clean and organized. Would recommend.',
                'Good service overall, but waiting time was long.',
                'Excellent facility with state-of-the-art equipment.',
                'The staff were very helpful and professional.',
                'Great experience from check-in to check-out.',
                'The facility is well-maintained and accessible.',
                'Impressed with the quality of care provided.',
                'Average facility, nothing special.',
                'Top-notch medical facility with excellent doctors.',
            ]),
            'is_visible' => fake()->boolean(90),
        ];
    }
}
