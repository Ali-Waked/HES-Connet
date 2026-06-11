<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        Role::create([
            'name' => 'super_admin',
        ]);
    }
}
