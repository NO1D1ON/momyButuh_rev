<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'babysitter_id'];
    public function messages() { return $this->hasMany(Message::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function babysitter() { return $this->belongsTo(Babysitter::class); }
    /**
     * Mendapatkan pesan terakhir dalam percakapan.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
