<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => ['en' => 'super_admin', 'ar' => 'مشرف عام'],
                'slug' => 'super_admin',
                'scope' => 'system',
                'is_system' => true,
            ]
        );

        $user = User::create([
            'name' => 'super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $user->systemRoles()->attach($role->id);
    }
}
