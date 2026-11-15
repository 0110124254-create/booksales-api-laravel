<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Super Admin', 'username' => 'superadmin', 'password' => 'password', 'is_admin' => true],
        );

        User::query()->updateOrCreate(
            ['email' => 'user@gmail.com'],
            ['name' => 'Demo User', 'username' => 'demouser', 'password' => 'password', 'is_admin' => false],
        );
    }
}
