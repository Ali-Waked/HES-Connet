<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityReview;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class FacilityReviewSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::all();
        $patientIds = Patient::pluck('id')->toArray();

        $comments = [
            'Well-equipped facility with friendly and professional staff.',
            'Clean and organized environment. Highly recommend this facility.',
            'Good service overall, but waiting time was longer than expected.',
            'Excellent facility with state-of-the-art medical equipment.',
            'The staff were very helpful and made me feel comfortable.',
            'Great experience from check-in to check-out. Very efficient.',
            'The facility is well-maintained and easily accessible.',
            'Impressed with the quality of care and attention to detail.',
            'Average facility, clean but could use some renovations.',
            'Top-notch medical facility with excellent and caring doctors.',
            'Very professional environment. Felt safe and well cared for.',
            'The facility exceeded my expectations. Very pleased with the service.',
            'Modern equipment and knowledgeable staff. Highly recommended.',
            'A bit crowded but the service quality makes up for it.',
            'Outstanding care from the moment I walked in. Thank you!',
        ];

        foreach ($facilities as $facility) {
            $usedPatientIds = [];
            $numReviews = min(fake()->numberBetween(2, 6), count($patientIds));

            for ($i = 0; $i < $numReviews; $i++) {
                $available = array_values(array_diff($patientIds, $usedPatientIds));
                if (empty($available)) {
                    break;
                }
                $patientId = $available[array_rand($available)];
                $usedPatientIds[] = $patientId;

                FacilityReview::create([
                    'facility_id' => $facility->id,
                    'patient_id' => $patientId,
                    'rating' => fake()->numberBetween(3, 5),
                    'comment' => $comments[array_rand($comments)],
                    'is_visible' => fake()->boolean(90),
                ]);
            }
        }
    }
}
