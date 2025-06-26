<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Babysitter;
use Illuminate\Support\Facades\Hash;

class BabysitterSeeder extends Seeder
{
    public function run(): void
    {
        Babysitter::create([
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '081234567890',
            'birth_date' => '1995-05-15',
            'address' => 'Jl. Gatot Subroto, Medan Petisah',
            'bio' => 'Berpengalaman 5 tahun merawat balita, sabar dan telaten.',
            'rate_per_hour' => 50000,
            'is_available' => true,
            'latitude' => 3.5896,
            'longitude' => 98.6657,
            'rating' => 4.8,
            'experience_years' => 5,
        ]);

        Babysitter::create([
            'name' => 'Bunga Lestari',
            'email' => 'bunga.lestari@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '081234567891',
            'birth_date' => '1998-08-20',
            'address' => 'Jl. Iskandar Muda, Medan Baru',
            'bio' => 'Lulusan D3 Keperawatan, bisa menangani anak berkebutuhan khusus.',
            'rate_per_hour' => 60000,
            'is_available' => true,
            'latitude' => 3.5843,
            'longitude' => 98.6534,
            'rating' => 4.9,
            'experience_years' => 3,
        ]);

        Babysitter::create([
            'name' => 'Dewi Sartika',
            'email' => 'dewi.sartika@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '081234567892',
            'birth_date' => '1997-01-10',
            'address' => 'Jl. Dr. Mansyur, Medan Selayang',
            'bio' => 'Menyukai kegiatan outdoor dan kreatif bersama anak-anak.',
            'rate_per_hour' => 45000,
            'is_available' => false,
            'latitude' => 3.5623,
            'longitude' => 98.6432,
            'rating' => 4.5,
            'experience_years' => 4,
        ]);
    }
}