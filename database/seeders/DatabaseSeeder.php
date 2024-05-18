<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        DB::table('roles')->insert([[
            'id' => '1',
            'name' => 'user',
            'description' => 'User',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'id' => '2',
            'name' => 'student',
            'description' => 'Student',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'id' => '3',
            'name' => 'manager',
            'description' => 'Manager',
            'created_at' => now(),
            'updated_at' => now(),
        ], [
            'id' => '4',
            'name' => 'admin',
            'description' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]]);
    }
}
