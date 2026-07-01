<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Models\Profession;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\User;
use App\Models\UserProfiles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $doctorProfession = Profession::where('slug', 'doctor')->first();
        $nurseProfession = Profession::where('slug', 'nurse')->first();
        $pharmacistProfession = Profession::where('slug', 'pharmacist')->first();
        $positions = StaffPosition::pluck('id')->toArray();

        $doctorUsers = User::whereIn('email', [
            'ahmad@example.com', 'lina@example.com',
            'mohammed@example.com', 'sarah@example.com',
        ])->get();

        $staffRecords = [
            ['user' => $doctorUsers->where('email', 'ahmad@example.com')->first(), 'specialization' => ['en' => 'Cardiology', 'ar' => 'أمراض القلب'], 'experience' => 15, 'fee' => 100],
            ['user' => $doctorUsers->where('email', 'lina@example.com')->first(), 'specialization' => ['en' => 'Pediatrics', 'ar' => 'طب الأطفال'], 'experience' => 12, 'fee' => 80],
            ['user' => $doctorUsers->where('email', 'mohammed@example.com')->first(), 'specialization' => ['en' => 'Neurology', 'ar' => 'الأعصاب'], 'experience' => 18, 'fee' => 120],
            ['user' => $doctorUsers->where('email', 'sarah@example.com')->first(), 'specialization' => ['en' => 'Dermatology', 'ar' => 'الأمراض الجلدية'], 'experience' => 10, 'fee' => 90],
        ];

        foreach ($staffRecords as $record) {
            if (! $record['user']) {
                continue;
            }
            Staff::create([
                'uuid' => Str::uuid(),
                'user_id' => $record['user']->id,
                'profession_id' => $doctorProfession?->id,
                'specialization' => $record['specialization'],
                'experience_years' => $record['experience'],
                'bio' => ['en' => 'Experienced medical professional dedicated to providing quality healthcare.', 'ar' => 'خبير طبي متمرس ملتزم بتقديم رعاية صحية عالية الجودة.'],
                'consultation_fee' => $record['fee'],
                'status' => AccountStatus::ACTIVE,
                'staff_position_id' => $positions[array_rand($positions)],
            ]);
        }

        // Create additional staff from existing users and new ones
        $existingStaffUserIds = collect($staffRecords)->pluck('user.id')->filter()->toArray();
        $remainingUsers = User::whereNotIn('id', $existingStaffUserIds)
            ->where('email', '!=', 'admin@gmail.com')
            ->take(8)
            ->get();

        $professionIds = Profession::pluck('id')->toArray();

        foreach ($remainingUsers as $user) {
            $isNurse = fake()->boolean(30);
            $isPharmacist = fake()->boolean(20);

            Staff::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'profession_id' => $isNurse ? ($nurseProfession?->id ?? $professionIds[array_rand($professionIds)]) : ($isPharmacist ? ($pharmacistProfession?->id ?? $professionIds[array_rand($professionIds)]) : $professionIds[array_rand($professionIds)]),
                'specialization' => ['en' => fake()->randomElement(['General Medicine', 'Emergency Medicine', 'Family Medicine']), 'ar' => fake('ar_SA')->randomElement(['طب عام', 'طب طوارئ', 'طب عائلي'])],
                'experience_years' => fake()->numberBetween(2, 20),
                'bio' => ['en' => fake()->paragraph(), 'ar' => fake('ar_SA')->paragraph()],
                'consultation_fee' => fake()->randomFloat(2, 30, 150),
                'status' => AccountStatus::ACTIVE,
                'staff_position_id' => $positions[array_rand($positions)],
            ]);
        }

        // Create remaining staff with new users to reach 20+
        Staff::factory()
            ->count(10)
            ->create()
            ->each(function (Staff $staff) {
                $profileImage = 'https://images.unsplash.com/photo-'.fake()->randomElement([
                    '1612349317150-e413f6a5b16d', '1594824476967-48c8b964273f',
                    '1537368910025-700350fe46c7', '1559839734-2b71ea197ec2',
                    '1507003211169-0a1dd7228f2d', '1524504388940-b1c63c8c0f52',
                ]).'?w=400';

                UserProfiles::updateOrCreate(
                    ['user_id' => $staff->user_id],
                    ['profile_image' => $profileImage]
                );
            });
    }
}
