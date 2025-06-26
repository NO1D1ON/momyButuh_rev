<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Kita buat properti publik agar data ini ikut terkirim dalam broadcast
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Mendapatkan channel tempat event akan disiarkan.
     */
    // public function broadcastOn(): array
    // {
    //     // Pesan akan disiarkan ke channel privat yang namanya unik untuk setiap percakapan.
    //     // Contoh: 'conversation.1', 'conversation.2', dst.
    //     return [
    //         new PrivateChannel('conversation.'.$this->message->conversation_id),
    //     ];
    // }

    public function broadcastAs(): string
    {
        return 'new.message'; // Kita beri nama alias 'new.message'
    }
}