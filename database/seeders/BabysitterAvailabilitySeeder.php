<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Babysitter;
use App\Models\BabysitterAvailability;

class BabysitterAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Data untuk 4 babysitter di sekitar Medan
        $babysittersData = [
            [
                'name' => 'Rina Hartati',
                'email' => 'rina.hartati@example.com',
                'address' => 'Jl. Juanda, Medan Polonia',
                'latitude' => 3.5721,
                'longitude' => 98.6695,
                'rate_per_hour' => 55000,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'address' => 'Jl. Sei Batang Hari, Medan Sunggal',
                'latitude' => 3.5910,
                'longitude' => 98.6450,
                'rate_per_hour' => 50000,
            ],
            [
                'name' => 'Bunga Citra',
                'email' => 'bunga.citra@example.com',
                'address' => 'Jl. Dr. Mansyur, Medan Baru',
                'latitude' => 3.5785,
                'longitude' => 98.6588,
                'rate_per_hour' => 60000,
            ],
            [
                'name' => 'Fitriani Lubis',
                'email' => 'fitriani.lubis@example.com',
                'address' => 'Jl. Pancing, Medan Tembung',
                'latitude' => 3.6102,
                'longitude' => 98.7181,
                'rate_per_hour' => 45000,
            ],
        ];

        // Loop untuk membuat setiap babysitter dan jadwal ketersediaannya
        foreach ($babysittersData as $data) {
            
            // PERBAIKAN 1: Gunakan updateOrCreate untuk menghindari error duplikasi email.
            // Metode ini akan mencari babysitter berdasarkan email. Jika ditemukan, akan di-update.
            // Jika tidak, akan dibuat data baru.
            $babysitter = Babysitter::updateOrCreate(
                ['email' => $data['email']], // Kunci unik untuk mencari data
                [ // Data untuk dibuat atau diperbarui
                    'name' => $data['name'],
                    'password' => Hash::make('password'), // Password default
                    'phone_number' => '0812345678' . rand(10, 99),
                    'birth_date' => '1998-01-01',
                    'address' => $data['address'],
                    'bio' => 'Saya adalah pengasuh yang sabar, berpengalaman, dan menyukai anak-anak.',
                    'rate_per_hour' => $data['rate_per_hour'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'rating' => round(rand(40, 50) / 10, 1),
                    'experience_years' => rand(2, 6),
                ]
            );

            // PERBAIKAN 2: Gunakan updateOrCreate juga untuk jadwal agar tidak duplikat
            // jika seeder dijalankan lebih dari sekali.
            BabysitterAvailability::updateOrCreate(
                [
                    'babysitter_id' => $babysitter->id,
                    'available_date' => '2025-07-01',
                ], // Kunci unik untuk mencari jadwal
                [ // Data jadwal untuk dibuat atau diperbarui
                    'start_time' => '18:00:00', 
                    'end_time' => '23:55:00',
                    'rate_per_hour' => $data['rate_per_hour'],
                    'location_preference' => 'Area ' . explode(',', $data['address'])[1],
                    'notes' => 'Siap bekerja sesuai jadwal.',
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]
            );
        }
    }
}
