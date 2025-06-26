<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Import HasApiTokens

class Babysitter extends Authenticatable
{
    use HasFactory, HasApiTokens; // Tambahkan HasApiTokens
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',  // <-- Tambahkan ini
        'phone_number',
        'birth_date',
        'address',
        'bio',
        'rate_per_hour',
        'is_available',
        'latitude',
        'longitude',
        'rating',             // <-- Tambahkan ini
        'experience_years',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function conversations() { return $this->hasMany(Conversation::class); }


    public function messages()
    {
        return $this->morphMany(\App\Models\Message::class, 'sender');
    }
}