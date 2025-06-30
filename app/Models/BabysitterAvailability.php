<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BabysitterAvailability extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * PASTIKAN SEMUA KOLOM INI ADA.
     * @var array
     */
    protected $fillable = [
        'babysitter_id',
        'available_date',
        'start_time',
        'end_time',
        'rate_per_hour',
        'location_preference',
        'notes',
    ];

    /**
     * Mendefinisikan relasi ke model Babysitter.
     */
    public function babysitter()
    {
        return $this->belongsTo(Babysitter::class);
    }
}