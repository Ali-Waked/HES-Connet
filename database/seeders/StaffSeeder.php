<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Models\Profession;
use App\Models\Specialization;
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
        $specializationIds = Specialization::pluck('id')->toArray();

        $doctorUsers = User::whereIn('email', [
            'ahmad@example.com', 'lina@example.com',
            'mohammed@example.com', 'sarah@example.com',
        ])->get();

        $specializationMap = [
            'cardiology' => Specialization::where('name->en', 'Cardiology')->value('id'),
            'pediatrics' => Specialization::where('name->en', 'Pediatrics')->value('id'),
            'neurology' => Specialization::where('name->en', 'Neurology')->value('id'),
            'dermatology' => Specialization::where('name->en', 'Dermatology')->value('id'),
        ];

        $staffRecords = [
            ['user' => $doctorUsers->where('email', 'ahmad@example.com')->first(), 'specialization_id' => $specializationMap['cardiology'], 'experience' => 15, 'fee' => 100],
            ['user' => $doctorUsers->where('email', 'lina@example.com')->first(), 'specialization_id' => $specializationMap['pediatrics'], 'experience' => 12, 'fee' => 80],
            ['user' => $doctorUsers->where('email', 'mohammed@example.com')->first(), 'specialization_id' => $specializationMap['neurology'], 'experience' => 18, 'fee' => 120],
            ['user' => $doctorUsers->where('email', 'sarah@example.com')->first(), 'specialization_id' => $specializationMap['dermatology'], 'experience' => 10, 'fee' => 90],
        ];

        foreach ($staffRecords as $record) {
            if (! $record['user']) {
                continue;
            }
            Staff::create([
                'uuid' => Str::uuid(),
                'user_id' => $record['user']->id,
                'profession_id' => $doctorProfession?->id,
                'specialization_id' => $record['specialization_id'],
                'experience_years' => $record['experience'],
                'bio' => ['en' => 'Experienced medical professional dedicated to providing quality healthcare.', 'ar' => 'خبير طبي متمرس ملتزم بتقديم رعاية صحية عالية الجودة.'],
                'consultation_fee' => $record['fee'],
                'status' => AccountStatus::ACTIVE,
                'staff_position_id' => $positions[array_rand($positions)],
            ]);
        }

        // Create additional staff from existing users
        $existingStaffUserIds = collect($staffRecords)->pluck('user.id')->filter()->toArray();
        $remainingUsers = User::whereNotIn('id', $existingStaffUserIds)
            ->where('email', '!=', 'admin@gmail.com')
            ->take(8)
            ->get();

        foreach ($remainingUsers as $user) {
            Staff::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'profession_id' => fake()->randomElement([$doctorProfession?->id, $nurseProfession?->id, $pharmacistProfession?->id]),
                'specialization_id' => $specializationIds[array_rand($specializationIds)],
                'experience_years' => fake()->numberBetween(2, 20),
                'bio' => ['en' => fake()->paragraph(), 'ar' => fake('ar_SA')->paragraph()],
                'consultation_fee' => fake()->randomFloat(2, 30, 150),
                'status' => AccountStatus::ACTIVE,
                'staff_position_id' => $positions[array_rand($positions)],
            ]);
        }

        // Create remaining staff with new users to reach ~2000
        $targetCount = 2000 - Staff::count();
        $chunkSize = 100;
        $chunks = (int) ceil($targetCount / $chunkSize);

        for ($c = 0; $c < $chunks; $c++) {
            $size = min($chunkSize, $targetCount - ($c * $chunkSize));
            if ($size <= 0) {
                break;
            }

            Staff::factory()
                ->count($size)
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
}
