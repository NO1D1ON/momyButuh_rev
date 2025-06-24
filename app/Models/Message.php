<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'sender_type', 'body', 'read_at'];
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function sender()
    {
        return $this->morphTo();
    }
}
