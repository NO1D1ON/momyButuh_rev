<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [

        'name', 'role', 'email', 'password', 'phone_number',
        'address', 'latitude', 'longitude', 'balance',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function topups() { return $this->hasMany(Topup::class); }

    public function conversations() { return $this->hasMany(Conversation::class); }

    public function favorites()
    {
        return $this->belongsToMany(\App\Models\Babysitter::class, 'favorites', 'user_id', 'babysitter_id');
    }

    // di dalam class User
    public function messages()
    {
        return $this->morphMany(\App\Models\Message::class, 'sender');
    }
}
