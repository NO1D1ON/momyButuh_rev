<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Anda bisa mengisi conversation_id di dalam test, atau biarkan factory membuatnya
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(), // Secara default, pengirim adalah User
            'sender_type' => 'App\Models\User',
            'body' => fake()->sentence(),
            'read_at' => null,
        ];
    }
}
