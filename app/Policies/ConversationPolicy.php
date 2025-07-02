<?php

namespace App\Policies;

use App\Models\Conversation;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the conversation and its messages.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \App\Models\Conversation  $conversation
     * @return bool
     */
    public function view(Authenticatable $user, Conversation $conversation)
    {
        // Menggunakan operator '==' untuk perbandingan yang tidak ketat (nilai saja).
        // Ini akan mengizinkan perbandingan antara integer dan string (misal: 2 == "2").
        return $user->id == $conversation->user_id || $user->id == $conversation->babysitter_id;
    }
}