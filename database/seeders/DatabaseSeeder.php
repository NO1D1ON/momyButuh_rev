<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil semua seeder yang ingin Anda jalankan di sini
        $this->call([
            // UserSeeder::class,
            // BabysitterSeeder::class, // <-- TAMBAHKAN BARIS INI
            BabysitterAvailabilitySeeder::class,

        ]);
    }
}