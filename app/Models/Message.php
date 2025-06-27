<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * PERBAIKAN: Mengosongkan array $guarded akan mengizinkan semua
     * atribut untuk diisi secara massal (mass assignable).
     * Ini akan memperbaiki error MassAssignmentException saat memanggil ->update().
     */
    protected $guarded = [];

    /**
     * Relasi polimorfik untuk pengirim pesan (bisa User atau Babysitter).
     */
    public function sender()
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke Conversation.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
