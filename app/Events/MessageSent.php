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

    /**
     * Properti publik ini akan otomatis disertakan dalam payload broadcast.
     * @var \App\Models\Message
     */
    public $message;

    /**
     * Buat instance event baru.
     *
     * @param \App\Models\Message $message
     * @return void
     */
    public function __construct(Message $message)
    {
        // Muat relasi sender agar data nama pengirim ikut terkirim
        $this->message = $message->load('sender');
    }

    /**
     * Channel tempat event ini akan disiarkan.
     * Pesan hanya akan dikirim ke channel percakapan yang spesifik.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * Nama alias untuk event broadcast.
     * Client akan mendengarkan event dengan nama ini.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'new.message';
    }

    /**
     * Data (payload) yang akan di-broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'body' => $this->message->body,
                'conversation_id' => $this->message->conversation_id,
                'created_at' => $this->message->created_at->toIso8601String(),
                'sender' => [
                    'id' => $this->message->sender->id,
                    'name' => $this->message->sender->name,
                    'type' => $this->message->sender_type,
                ]
            ]
        ];
    }
}