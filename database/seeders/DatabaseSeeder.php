<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

       Role::insert([[
            'id' => '1',
            'name' => 'user',
            'description' => 'User',
        ], [
            'id' => '2',
            'name' => 'student',
            'description' => 'Student',
        ], [
            'id' => '3',
            'name' => 'manager',
            'description' => 'Manager',
        ], [
            'id' => '4',
            'name' => 'admin',
            'description' => 'Administrator',
        ]]);
    }
}
