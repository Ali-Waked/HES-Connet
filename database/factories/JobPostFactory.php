<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApplyMethod;
use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\JobStatus;
use App\Models\Category;
use App\Models\Facility;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JobPostFactory extends Factory
{
    protected $model = JobPost::class;

    public function definition(): array
    {
        $titles = [
            'General Practitioner', 'Pediatrician', 'Cardiologist', 'Registered Nurse',
            'Pharmacist', 'Lab Technician', 'Radiologist', 'Backend Developer',
            'Frontend Developer', 'Medical Secretary', 'Accountant', 'HR Manager',
            'IT Support Specialist', 'Facility Manager', 'Dentist', 'Orthopedic Surgeon',
            'Dermatologist', 'Ophthalmologist', 'Physical Therapist', 'Social Worker',
        ];

        return [
            'uuid' => Str::uuid(),
            'facility_id' => Facility::factory(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => [
                'en' => fake()->randomElement($titles),
                'ar' => fake('ar_SA')->jobTitle(),
            ],
            'content' => [
                'en' => fake()->paragraphs(4, true),
                'ar' => fake('ar_SA')->paragraphs(4, true),
            ],
            'apply_method' => fake()->randomElement([ApplyMethod::EMAIL, ApplyMethod::EMAIL, ApplyMethod::LINK]),
            'apply_value' => fake()->email(),
            'employment_type' => fake()->randomElement(EmploymentType::cases()),
            'experience_level' => fake()->randomElement(ExperienceLevel::cases()),
            'location' => fake()->city(),
            'salary_from' => fake()->randomFloat(2, 1000, 5000),
            'salary_to' => fake()->randomFloat(2, 5000, 15000),
            'salary_currency' => 'USD',
            'is_salary_visible' => fake()->boolean(70),
            'vacancies' => fake()->numberBetween(1, 5),
            'views' => fake()->numberBetween(0, 2000),
            'featured' => fake()->boolean(15),
            'cover_image' => null,
            'status' => JobStatus::APPROVED,
            'rejected_reason' => null,
            'published_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => JobStatus::APPROVED,
            'published_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ]);
    }
}
