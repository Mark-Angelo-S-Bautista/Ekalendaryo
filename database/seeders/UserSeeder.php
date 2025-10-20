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

        // Create an 'editor' user
        User::create([
            'name' => 'Mark Angelo',
            'userId' => 'MA22013875', // The username for login
            'email' => 'gelobautista0420@gmail.com',
            'phoneNum' => '09537329603',
            'role' => 'Editor',
            'password' => Hash::make('password'), // The password is 'password'
            
        ]);

        // Create a 'UserManagement' user
        User::create([
            'name' => 'Juan DelaCruz',
            'userId' => 'MA22013876', // The username for login
            'email' => 'juan@gmail.com',
            'phoneNum' => '09537329604',
            'role' => 'UserManagement',
            'password' => Hash::make('password'), // The password is 'password'
        ]);
    }
}