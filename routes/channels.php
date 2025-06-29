

<?php
use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversation}', function ($user, Conversation $conversation) {
    // Cek apakah pengguna (User atau Babysitter) adalah bagian dari percakapan
    if ($user->id === $conversation->user_id || $user->id === $conversation->babysitter_id) {
        // Untuk presence channel, kembalikan data pengguna
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
