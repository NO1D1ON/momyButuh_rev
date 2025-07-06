<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
    use HasFactory;
    // Ganti 'job_date' dengan 'start_date' dan 'end_date'
    protected $fillable = [
        'user_id', 'title', 'description', 'location_address', 'latitude', 'longitude',
        'start_date', 'end_date', 'start_time', 'end_time', 'offered_price', 'status'
    ];
    public function user() { return $this->belongsTo(User::class); }
}