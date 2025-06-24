<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'babysitter_id'];
    public function messages() { return $this->hasMany(Message::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function babysitter() { return $this->belongsTo(Babysitter::class); }
}
