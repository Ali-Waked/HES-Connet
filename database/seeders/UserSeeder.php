<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\GenderType;
use App\Enums\Provider;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfiles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => ['en' => 'Ahmad Khalil', 'ar' => 'أحمد خليل'],
                'email' => 'ahmad@example.com',
                'role' => 'doctor_portal_user',
                'gender' => 'male',
                'phone' => '+970599100001',
                'profile_image' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400',
            ],
            [
                'name' => ['en' => 'Lina Odeh', 'ar' => 'لينا عودة'],
                'email' => 'lina@example.com',
                'role' => 'doctor_portal_user',
                'gender' => 'female',
                'phone' => '+970599100002',
                'profile_image' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=400',
            ],
            [
                'name' => ['en' => 'Mohammed Abu Salem', 'ar' => 'محمد أبو سالم'],
                'email' => 'mohammed@example.com',
                'role' => 'doctor_portal_user',
                'gender' => 'male',
                'phone' => '+970599100003',
                'profile_image' => 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=400',
            ],
            [
                'name' => ['en' => 'Sarah Hamdan', 'ar' => 'سارة حمدان'],
                'email' => 'sarah@example.com',
                'role' => 'doctor_portal_user',
                'gender' => 'female',
                'phone' => '+970599100004',
                'profile_image' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400',
            ],
            [
                'name' => ['en' => 'Fatima Alami', 'ar' => 'فاطمة العلمي'],
                'email' => 'fatima@example.com',
                'role' => 'patient_portal_user',
                'gender' => 'female',
                'phone' => '+970599100005',
            ],
            [
                'name' => ['en' => 'Omar Hassan', 'ar' => 'عمر حسن'],
                'email' => 'omar@example.com',
                'role' => 'patient_portal_user',
                'gender' => 'male',
                'phone' => '+970599100006',
            ],
            [
                'name' => ['en' => 'Nadia Yousef', 'ar' => 'نادية يوسف'],
                'email' => 'nadia@example.com',
                'role' => 'patient_portal_user',
                'gender' => 'female',
                'phone' => '+970599100007',
            ],
            [
                'name' => ['en' => 'Khaled Abu Rida', 'ar' => 'خالد أبو ريدة'],
                'email' => 'khaled@example.com',
                'role' => 'patient_portal_user',
                'gender' => 'male',
                'phone' => '+970599100008',
            ],
            [
                'name' => ['en' => 'Layla Mansour', 'ar' => 'ليلى منصور'],
                'email' => 'layla@example.com',
                'role' => 'facility_admin',
                'gender' => 'female',
                'phone' => '+970599100009',
            ],
            [
                'name' => ['en' => 'Hani Qasim', 'ar' => 'هاني قاسم'],
                'email' => 'hani@example.com',
                'role' => 'facility_admin',
                'gender' => 'male',
                'phone' => '+970599100010',
            ],
            [
                'name' => ['en' => 'Mariam Shihab', 'ar' => 'مريم شهاب'],
                'email' => 'mariam@example.com',
                'role' => 'pharmacy_portal_user',
                'gender' => 'female',
                'phone' => '+970599100011',
            ],
            [
                'name' => ['en' => 'Tariq Jaber', 'ar' => 'طارق جابر'],
                'email' => 'tariq@example.com',
                'role' => 'patient_portal_user',
                'gender' => 'male',
                'phone' => '+970599100012',
            ],
            [
                'name' => ['en' => 'Dina Rizq', 'ar' => 'دينا رزق'],
                'email' => 'dina@example.com',
                'role' => 'content_manager',
                'gender' => 'female',
                'phone' => '+970599100013',
            ],
            [
                'name' => ['en' => 'Samir Khalifa', 'ar' => 'سمير خليفة'],
                'email' => 'samir@example.com',
                'role' => 'finance_manager',
                'gender' => 'male',
                'phone' => '+970599100014',
            ],
            [
                'name' => ['en' => 'Reem Akkad', 'ar' => 'ريم عكاد'],
                'email' => 'reem@example.com',
                'role' => 'patient_portal_user',
                'gender' => 'female',
                'phone' => '+970599100015',
            ],
        ];

        foreach ($users as $data) {
            $roleSlug = $data['role'];
            $gender = $data['gender'];
            $profileImage = $data['profile_image'] ?? null;
            unset($data['role'], $data['gender'], $data['profile_image']);

            $user = User::create([
                'uuid' => Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'locale' => 'ar',
                'provider' => Provider::LOCAL,
                'remember_token' => Str::random(10),
            ]);

            UserProfiles::create([
                'user_id' => $user->id,
                'phone' => $data['phone'],
                'gender' => $gender === 'male' ? GenderType::MALE : GenderType::FEMALE,
                'birth_date' => fake()->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
                'address' => fake()->address(),
                'profile_image' => $profileImage,
            ]);

            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->systemRoles()->attach($role->id);
            }
        }

        User::factory()->count(15)->create()->each(function (User $user) {
            $profileImage = fake()->boolean(50)
                ? 'https://images.unsplash.com/photo-'.fake()->randomElement([
                    '1507003211169-0a1dd7228f2d', '1494790108377-be9c29b29330',
                    '1500648767791-00dcc994a43e', '1438761681033-6461ffad8d80',
                    '1472099645785-5658abf4ff4e', '1534528741775-53994a69daeb',
                    '1507003211169-0a1dd7228f2d', '1524504388940-b1c63c8c0f52',
                ]).'?w=400'
                : null;

            UserProfiles::create([
                'user_id' => $user->id,
                'phone' => fake()->phoneNumber(),
                'gender' => fake()->randomElement([GenderType::MALE, GenderType::FEMALE]),
                'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'address' => fake()->address(),
                'profile_image' => $profileImage,
            ]);
        });
    }
}
