<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin MomyButuh',
            'role' => 'admin',
            'email' => 'admin@momybutuh.com',
            'password' => Hash::make('password'), // passwordnya adalah 'password'
        ]);

        // database/seeders/UserSeeder.php
        User::create([
            'name' => 'Admin MomyButuh',
            'role' => 'admin',
            'email' => 'admin@momybutuh.com',
            'password' => Hash::make('password'),
            'address' => 'Jl. Krakatau No. 1, Medan', // Contoh alamat
            'latitude' => 3.614, // Contoh latitude
            'longitude' => 98.692, // Contoh longitude
        ]);
    }
}