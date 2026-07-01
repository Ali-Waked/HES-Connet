<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = config('permissions');

        foreach ($modules as $module => $permissions) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['key' => $permission['key']],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $permission['name'],
                        'description' => $permission['description'],
                    ]
                );
            }
        }
    }
}
