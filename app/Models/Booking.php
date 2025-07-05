<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'babysitter_id', 'booking_date', 'start_time', 'end_time', 'total_price', 'status', 'parent_approved', 'babysitter_approved',];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function babysitter() {
        return $this->belongsTo(Babysitter::class);
    }
}