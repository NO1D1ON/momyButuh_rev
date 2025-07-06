<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BabysitterAvailability extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'babysitter_id',
        'available_date',
        'start_time',
        'end_time',
        'rate_per_hour',
        'location_preference',
        'notes',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'available_date' => 'date',
        'rate_per_hour' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the babysitter that owns the availability.
     */
    public function babysitter()
    {
        return $this->belongsTo(Babysitter::class);
    }
}
