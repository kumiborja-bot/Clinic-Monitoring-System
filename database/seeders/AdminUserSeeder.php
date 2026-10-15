<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kumiborja@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'mmaciclinic54@gmail.com'],
            [
                'name' => 'Nurse',
                'password' => bcrypt('password'),
                'role' => 'nurse',
            ]
        );
    }
}
