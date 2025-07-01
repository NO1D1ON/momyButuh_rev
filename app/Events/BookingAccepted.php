<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use App\Models\Notification;

class BookingAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(Booking $booking)
    {
        $parent = $booking->user; // Dapatkan objek User (orang tua)

        // Buat instance dari kelas Notifikasi Anda
        $notificationPayload = new BookingHasBeenAccepted($booking);

        // Biarkan Laravel yang menangani penyimpanan ke database
        $parent->notify($notificationPayload);
        
        // Ambil notifikasi yang baru dibuat untuk disiarkan
        $this->notification = $parent->notifications()->latest()->first();
    }

    /**
     * Tentukan channel privat untuk user yang bersangkutan.
     */
    public function broadcastOn(): array
    {
        // Nama channel harus unik per pengguna
        return [
            new PrivateChannel('notifications.' . $this->notification->user_id),
        ];
    }

    /**
     * Nama event yang akan didengarkan oleh frontend.
     */
    public function broadcastAs(): string
    {
        return 'new.notification';
    }
}