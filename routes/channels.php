<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// LOGIKA BARU UNTUK OTORISASI CHANNEL PERCAKAPAN
Broadcast::channel('conversation.{conversation}', function ($user, Conversation $conversation) {
    // Izinkan user untuk mendengarkan channel ini HANYA JIKA
    // ID user tersebut sama dengan user_id (Orang Tua) ATAU babysitter_id di dalam data percakapan.
    // Catatan: Karena kita belum membuat login untuk babysitter, untuk saat ini kita hanya cek user_id.
    return $user->id === $conversation->user_id;
});

