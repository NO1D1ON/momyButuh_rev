<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Babysitter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'birth_date',
        'address',
        'bio',
        'rate_per_hour',
        'is_available',
    ];

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function conversations() { return $this->hasMany(Conversation::class); }

    public function messages()
    {
        return $this->morphMany(Message::class, 'sender');
    }
}