<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Review;
use App\Models\Service;
use App\Models\AvailableSchedule;
use App\Models\Conversation;
use App\Models\Booking;
use App\Models\Message;
use App\Models\BabysitterAvailability;
use Carbon\Carbon;

class Babysitter extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['age'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'birth_date', 'address',
        'latitude', 'longitude', 'bio', 'rate_per_hour', 'rating',
        'experience_years', 'is_available', 'balance', 'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'rate_per_hour' => 'integer',
        'experience_years' => 'integer',
        'balance' => 'float',
        'rating' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_available' => 'boolean',
    ];

    /**
     * Get the user's age.
     *
     * @return int
     */
    public function getAgeAttribute()
    {
        if ($this->birth_date) {
            return Carbon::parse($this->birth_date)->age;
        }
        return 0; // Default value if birth_date is not set
    }

    /**
     * Get the bookings for the babysitter.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the conversations for the babysitter.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get all of the babysitter's messages.
     */
    public function messages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    /**
     * Get the reviews for the babysitter.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * The services that belong to the babysitter.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * Get the available schedules for the babysitter.
     * @deprecated Use availabilities() instead.
     */
    public function availableSchedules()
    {
        return $this->hasMany(AvailableSchedule::class);
    }

    /**
     * Get the availabilities for the babysitter.
     */
    public function availabilities()
    {
        return $this->hasMany(BabysitterAvailability::class);
    }
}
