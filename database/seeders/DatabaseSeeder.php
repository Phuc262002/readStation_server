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
