<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'babysitter_id',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'parent_approved',
        'babysitter_approved',
        'parent_confirmed_at',      // <-- DITAMBAHKAN
        'babysitter_confirmed_at',  // <-- DITAMBAHKAN
    ];

    /**
     * The attributes that should be cast.
     * Ini akan memastikan tipe data selalu benar.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_price' => 'integer',
        'parent_approved' => 'boolean',
        'babysitter_approved' => 'boolean',
        'parent_confirmed_at' => 'datetime',
        'babysitter_confirmed_at' => 'datetime',
    ];

    /**
     * Mendapatkan data User (Orang Tua) yang memiliki booking ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan data Babysitter yang memiliki booking ini.
     */
    public function babysitter()
    {
        return $this->belongsTo(Babysitter::class);
    }

    /**
     * Mendapatkan data Review untuk booking ini (jika ada).
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
