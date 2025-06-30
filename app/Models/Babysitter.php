<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Review;
use App\Models\Service;
use App\Models\AvailableSchedule;
use App\Models\Conversation;
use App\Models\Booking;
use App\Models\Message; // Import HasApiTokens
use Carbon\Carbon;

class Babysitter extends Authenticatable
{
    use HasFactory, HasApiTokens; // Tambahkan HasApiTokens

    protected $appends = ['age'];
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

     /**
     * Relasi ke Review: Seorang babysitter bisa memiliki banyak review.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relasi ke Service: Seorang babysitter bisa menawarkan banyak service.
     * Diasumsikan ini adalah relasi many-to-many.
     */
    public function services()
    {
        // Sesuaikan dengan nama tabel pivot Anda jika berbeda (contoh: 'babysitter_service')
        return $this->belongsToMany(Service::class);
    }

    /**
     * Relasi ke Jadwal Tersedia: Seorang babysitter memiliki banyak jadwal tersedia.
     */
    public function availableSchedules()
    {
        return $this->hasMany(AvailableSchedule::class);
    }

    public function getAgeAttribute() // TAMBAHKAN FUNGSI INI
    {
        if ($this->birth_date) {
            return Carbon::parse($this->birth_date)->age;
        }
        return 0; // Beri nilai default jika tanggal lahir tidak ada
    }

    public function availabilities()
    {
        return $this->hasMany(BabysitterAvailability::class);
    }
}