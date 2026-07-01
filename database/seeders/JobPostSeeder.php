<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ApplyMethod;
use App\Enums\EmploymentType;
use App\Enums\ExperienceLevel;
use App\Enums\JobStatus;
use App\Models\Category;
use App\Models\Facility;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JobPostSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::all();
        $userIds = User::pluck('id')->toArray();
        $jobCategories = Category::where('type', 'job')->get();

        $jobs = [
            ['title' => ['en' => 'General Practitioner', 'ar' => 'طبيب عام'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 3000, 'salary_to' => 6000],
            ['title' => ['en' => 'Pediatrician', 'ar' => 'طبيب أطفال'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 4000, 'salary_to' => 8000],
            ['title' => ['en' => 'Cardiologist', 'ar' => 'طبيب قلب'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 5000, 'salary_to' => 10000],
            ['title' => ['en' => 'Registered Nurse', 'ar' => 'ممرض'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 1500, 'salary_to' => 3000],
            ['title' => ['en' => 'Pharmacist', 'ar' => 'صيدلي'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 2000, 'salary_to' => 4000],
            ['title' => ['en' => 'Lab Technician', 'ar' => 'فني مختبر'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::JUNIOR, 'salary_from' => 1200, 'salary_to' => 2500],
            ['title' => ['en' => 'Radiologist', 'ar' => 'أخصائي أشعة'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 3500, 'salary_to' => 7000],
            ['title' => ['en' => 'Backend Developer', 'ar' => 'مطور باك إند'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 2500, 'salary_to' => 5000],
            ['title' => ['en' => 'Frontend Developer', 'ar' => 'مطور فرونت إند'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 2000, 'salary_to' => 4500],
            ['title' => ['en' => 'Medical Secretary', 'ar' => 'سكرتير طبي'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::ENTRY, 'salary_from' => 800, 'salary_to' => 1500],
            ['title' => ['en' => 'Accountant', 'ar' => 'محاسب'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 1500, 'salary_to' => 3000],
            ['title' => ['en' => 'HR Manager', 'ar' => 'مدير موارد بشرية'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 2500, 'salary_to' => 5000],
            ['title' => ['en' => 'Dentist', 'ar' => 'طبيب أسنان'], 'type' => EmploymentType::PART_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 2000, 'salary_to' => 4000],
            ['title' => ['en' => 'Orthopedic Surgeon', 'ar' => 'جراح عظام'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::LEAD, 'salary_from' => 6000, 'salary_to' => 12000],
            ['title' => ['en' => 'Physical Therapist', 'ar' => 'أخصائي علاج طبيعي'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 1500, 'salary_to' => 3000],
            ['title' => ['en' => 'Social Worker', 'ar' => 'أخصائي اجتماعي'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::MID, 'salary_from' => 1200, 'salary_to' => 2500],
            ['title' => ['en' => 'IT Support Specialist', 'ar' => 'أخصائي دعم تقني'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::JUNIOR, 'salary_from' => 1000, 'salary_to' => 2000],
            ['title' => ['en' => 'Dermatologist', 'ar' => 'طبيب جلدية'], 'type' => EmploymentType::PART_TIME, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 3000, 'salary_to' => 6000],
            ['title' => ['en' => 'Ophthalmologist', 'ar' => 'طبيب عيون'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 4000, 'salary_to' => 8000],
            ['title' => ['en' => 'Facility Manager', 'ar' => 'مدير منشأة'], 'type' => EmploymentType::FULL_TIME, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 3000, 'salary_to' => 6000],
            ['title' => ['en' => 'Emergency Medicine Doctor', 'ar' => 'طبيب طوارئ'], 'type' => EmploymentType::CONTRACT, 'level' => ExperienceLevel::SENIOR, 'salary_from' => 4500, 'salary_to' => 9000],
            ['title' => ['en' => 'Nutritionist', 'ar' => 'أخصائي تغذية'], 'type' => EmploymentType::PART_TIME, 'level' => ExperienceLevel::JUNIOR, 'salary_from' => 1000, 'salary_to' => 2000],
        ];

        foreach ($jobs as $data) {
            $facility = $facilities->random();
            $category = $jobCategories->random();
            $userId = $userIds[array_rand($userIds)];

            JobPost::create([
                'facility_id' => $facility->id,
                'user_id' => $userId,
                'category_id' => $category->id,
                'slug' => Str::slug($data['title']['en']).'-'.Str::lower(Str::random(6)),
                'title' => $data['title'],
                'content' => [
                    'en' => 'We are looking for an experienced professional to join our team at '.($facility->name['en'] ?? 'our facility').". The ideal candidate will have a strong background in their field and a commitment to providing excellent patient care.\n\nResponsibilities include:\n- Providing high-quality medical services\n- Collaborating with the healthcare team\n- Maintaining accurate patient records\n- Participating in continuing education\n\nQualifications:\n- Relevant degree and licensure\n- Minimum ".fake()->numberBetween(2, 5)." years of experience\n- Strong communication skills\n- Ability to work in a team environment",
                    'ar' => 'نبحث عن محترف ذو خبرة للانضمام إلى فريقنا في '.($facility->name['ar'] ?? 'مرفقنا').". المرشح المثالي يجب أن يكون لديه خلفية قوية في مجاله والتزام بتقديم رعاية ممتازة للمرضى.\n\nالمسؤوليات تشمل:\n- تقديم خدمات طبية عالية الجودة\n- التعاون مع فريق الرعاية الصحية\n- الحفاظ على سجلات دقيقة للمرضى\n- المشاركة في التعليم المستمر\n\nالمؤهلات:\n- الدرجة العلمية المناسبة والترخيص\n- خبرة لا تقل عن ".fake()->numberBetween(2, 5)." سنوات\n- مهارات تواصل قوية\n- القدرة على العمل في بيئة فريق",
                ],
                'apply_method' => fake()->randomElement([ApplyMethod::EMAIL, ApplyMethod::EMAIL, ApplyMethod::LINK]),
                'apply_value' => 'hr@'.Str::slug($facility->name['en'] ?? 'facility').'.com',
                'employment_type' => $data['type'],
                'experience_level' => $data['level'],
                'location' => $facility->city?->name['en'] ?? 'Gaza',
                'salary_from' => $data['salary_from'],
                'salary_to' => $data['salary_to'],
                'salary_currency' => 'USD',
                'is_salary_visible' => true,
                'vacancies' => fake()->numberBetween(1, 3),
                'views' => fake()->numberBetween(50, 1500),
                'featured' => fake()->boolean(20),
                'cover_image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800',
                'status' => fake()->randomElement([JobStatus::APPROVED, JobStatus::APPROVED, JobStatus::APPROVED, JobStatus::PENDING]),
                'rejected_reason' => null,
                'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
                'end_date' => now()->addDays(fake()->numberBetween(30, 90))->format('Y-m-d'),
            ]);
        }
    }
}
