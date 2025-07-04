<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Topup extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'amount', 'status', 'payment_proof', 'admin_notes'];
    public function user() { return $this->belongsTo(User::class); }
}