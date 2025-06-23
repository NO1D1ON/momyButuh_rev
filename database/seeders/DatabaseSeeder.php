<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil UserSeeder di sini
        $this->call([
            UserSeeder::class,
        ]);
    }
}