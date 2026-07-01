<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            [
                'name' => ['en' => 'Ministry of Health - Palestine', 'ar' => 'وزارة الصحة الفلسطينية'],
                'type' => 'government',
                'users' => ['admin@gmail.com'],
            ],
            [
                'name' => ['en' => 'UNRWA Health Program', 'ar' => 'برنامج الصحة لوكالة الغوث'],
                'type' => 'un_agency',
                'users' => [],
            ],
            [
                'name' => ['en' => 'Palestinian Medical Relief Society', 'ar' => 'جمعية الإغاثة الطبية الفلسطينية'],
                'type' => 'local_ngo',
                'users' => [],
            ],
            [
                'name' => ['en' => 'Doctors Without Borders - Gaza', 'ar' => 'أطباء بلا حدود - غزة'],
                'type' => 'international_ngo',
                'users' => [],
            ],
            [
                'name' => ['en' => 'Al-Quds Healthcare Network', 'ar' => 'شبكة القدس للرعاية الصحية'],
                'type' => 'private',
                'users' => [],
            ],
            [
                'name' => ['en' => 'World Health Organization - Palestine', 'ar' => 'منظمة الصحة العالمية - فلسطين'],
                'type' => 'un_agency',
                'users' => [],
            ],
        ];

        foreach ($organizations as $data) {
            $userEmails = $data['users'];
            unset($data['users']);

            $org = Organization::create([
                'uuid' => Str::uuid(),
                'name' => $data['name'],
                'type' => $data['type'],
            ]);

            foreach ($userEmails as $email) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    OrganizationUser::create([
                        'user_id' => $user->id,
                        'organization_id' => $org->id,
                        'status' => 'owner',
                    ]);
                }
            }
        }
    }
}
