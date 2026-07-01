<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $completedAppointments = Appointment::where('status', AppointmentStatus::COMPLETED)->get();

        $reviewContents = [
            'Excellent doctor, very knowledgeable and caring. He took the time to explain everything.',
            'Great experience, the staff was very professional and the doctor was thorough.',
            'Good service but had to wait a long time before being seen.',
            'Very satisfied with the treatment I received. Would definitely come back.',
            'The doctor listened carefully and explained my condition in detail.',
            'Average experience. The doctor was fine but the facility could be better.',
            'Highly recommend this doctor. Very professional and compassionate.',
            'Professional and compassionate care. Felt very comfortable throughout.',
            'The best medical experience I have ever had. Truly exceptional care.',
            'Kind staff and excellent medical advice. Very happy with the service.',
            'The doctor was very thorough and answered all my questions patiently.',
            'I felt heard and respected. The treatment plan was clear and effective.',
            'Wonderful bedside manner. Made me feel at ease immediately.',
            'Very knowledgeable and up-to-date with the latest medical research.',
            'Excellent follow-up care. The doctor checked on my progress regularly.',
        ];

        foreach ($completedAppointments as $index => $appointment) {
            if (fake()->boolean(60)) {
                $review = Review::create([
                    'uuid' => Str::uuid(),
                    'staff_id' => $appointment->facilityStaff?->staff_id,
                    'patient_id' => $appointment->patient_id,
                    'appointment_id' => $appointment->id,
                    'rating' => fake()->numberBetween(3, 5),
                    'content' => $reviewContents[$index % count($reviewContents)],
                ]);

                // Add reply to some reviews
                if (fake()->boolean(30)) {
                    ReviewReply::create([
                        'uuid' => Str::uuid(),
                        'review_id' => $review->id,
                        'reply' => fake()->randomElement([
                            'Thank you for your kind words! We appreciate your feedback.',
                            'We are glad you had a great experience. Thank you!',
                            'Thank you for choosing us. We look forward to seeing you again.',
                            'Your feedback means a lot to us. Thank you!',
                            'We are happy to hear about your positive experience.',
                        ]),
                    ]);
                }
            }
        }
    }
}
