<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User; // Bisa juga Babysitter, tergantung implementasi guard Anda
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $conversation;

    /**
     * Buat instance event baru.
     */
    public function __construct($user, Conversation $conversation)
    {
        $this->user = $user;
        $this->conversation = $conversation;
    }

    /**
     * Channel tempat event akan disiarkan.
     */
    public function broadcastOn(): array
    {
        // Gunakan PresenceChannel yang sama dengan otorisasi
        return [
            new PresenceChannel('conversation.' . $this->conversation->id),
        ];
    }

    /**
     * Nama alias untuk event.
     */
    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    /**
     * Data yang akan disiarkan.
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]
        ];
    }
}
