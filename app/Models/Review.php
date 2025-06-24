<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['booking_id', 'user_id', 'babysitter_id', 'rating', 'comment'];
    public function user() { return $this->belongsTo(User::class); }
    public function babysitter() { return $this->belongsTo(Babysitter::class); }
}
