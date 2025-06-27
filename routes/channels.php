<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Otorisasi presence channel untuk sebuah percakapan.
 *
 * Menggunakan PresenceChannel memungkinkan client untuk mengetahui siapa saja
 * yang sedang berada di dalam channel yang sama.
 * Jika otorisasi berhasil, kembalikan array data pengguna.
 */
Broadcast::channel('conversation.{conversation}', function ($user, Conversation $conversation) {
    // Cek apakah pengguna yang diautentikasi adalah bagian dari percakapan
    if ($user->id === $conversation->user_id || $user->id === $conversation->babysitter_id) {
        // Jika berhasil, kembalikan data user untuk presence channel
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false; // Jika tidak, tolak akses
});
