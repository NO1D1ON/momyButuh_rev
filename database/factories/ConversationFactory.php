<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Babysitter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Factory ini akan secara otomatis membuat User dan Babysitter baru
            // setiap kali sebuah Conversation dibuat untuk pengujian.
            'user_id' => User::factory(),
            'babysitter_id' => Babysitter::factory(),
        ];
    }
}
