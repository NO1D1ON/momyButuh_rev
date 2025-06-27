<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['babysitter_id', 'day_of_week', 'start_time', 'end_time'];

    public function babysitter()
    {
        return $this->belongsTo(Babysitter::class);
    }
}