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
            'department' => 'BSIS/ACT',
            'yearlevel' => '4thYear',
            'section' => 'B',
            'role' => 'Editor',
            'password' => Hash::make('password'), // The password is 'password'
            
        ]);

        // Create a 'UserManagement' user
        User::create([
            'name' => 'Juan DelaCruz',
            'userId' => 'MA22013876', // The username for login
            'email' => 'juan@gmail.com',
            'department' => 'BSIS/ACT',
            'yearlevel' => '4thYear',
            'section' => 'B',
            'role' => 'UserManagement',
            'password' => Hash::make('password'), // The password is 'password'
        ]);

        User::create([
            'name' => 'Joenel Valeton',
            'userId' => 'MA22013877', // The username for login
            'email' => 'nel@gmail.com',
            'department' => 'BSOM',
            'yearlevel' => '1stYear',
            'section' => 'D',
            'role' => 'Editor',
            'password' => Hash::make('password'), // The password is 'password'
        ]);

        User::create([
            'name' => 'Ella Mae DeCastro',
            'userId' => 'MA22013878', // The username for login
            'email' => 'ella@gmail.com',
            'department' => 'BSAIS',
            'yearlevel' => '2ndYear',
            'section' => 'B',
            'role' => 'UserManagement',
            'password' => Hash::make('password'), // The password is 'password'
        ]);

        User::create([
            'name' => 'Kenji Dela Cruz',
            'userId' => 'MA22013879', // The username for login
            'email' => 'kenj@gmail.com',
            'department' => 'BTVTED',
            'yearlevel' => '3rdYear',
            'section' => 'A',
            'role' => 'Viewer',
            'password' => Hash::make('password'), // The password is 'password'
        ]);
    }
}