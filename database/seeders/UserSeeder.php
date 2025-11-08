<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear the table before seeding to avoid duplicates
        User::truncate();

        // Create a 'UserManagement' user
        User::create([
            'name' => 'Mark Angelo Bautista',
            'title' => 'Offices',
            'office_name' => 'Registrar',
            'userId' => 'MA22013875', // The username for login
            'email' => 'juan@gmail.com',
            'department' => 'OFFICES',
            'role' => 'UserManagement',
            'password' => Hash::make('password'), // The password is 'password'
        ]);
    }
}