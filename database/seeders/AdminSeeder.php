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
            ['name->en' => 'super_admin'],
            ['name' => ['en' => 'super_admin', 'ar' => 'مشرف عام']]
        );

        User::create([
            'name' => 'super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
        ]);
    }
}
