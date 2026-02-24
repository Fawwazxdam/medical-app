<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create(
            [
                'name' => 'Admin',
                'email' => 'admin@g.c',
                'role' => 'admin',
            ]
        );

        User::factory()->create(
            [
                'name' => 'Doctor',
                'email' => 'doctor@g.c',
                'role' => 'doctor',
            ]
        );
    }
}
