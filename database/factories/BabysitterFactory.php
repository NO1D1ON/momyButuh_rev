<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Babysitter>
 */
class BabysitterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone_number' => $this->faker->phoneNumber(),
            'birth_date' => $this->faker->date(),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'bio' => $this->faker->paragraph(),
            'rate_per_hour' => $this->faker->numberBetween(50000, 150000),
            'rating' => $this->faker->randomFloat(1, 0, 5),
            'experience_years' => $this->faker->numberBetween(0, 10),
            'is_available' => $this->faker->boolean(90), // 90% kemungkinan tersedia
        ];
    }
}
