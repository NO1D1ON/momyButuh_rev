<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BabysitterBookingController extends Controller
{
    /**
     * Mengambil riwayat booking untuk babysitter yang sedang login.
     * Versi ini sudah diperbaiki untuk menangani data relasi yang hilang.
     */
    public function index()
    {
        $babysitter = Auth::user();

        $bookings = $babysitter->bookings()
            ->with('user:id,name,address') 
            ->whereIn('status', ['pending', 'confirmed', 'completed', 'rejected', 'parent_confirmed'])
            ->orderBy('booking_date', 'desc')
            ->get();

        // --- PERBAIKAN DI SINI ---
        // Langsung kembalikan collection $bookings. Laravel akan otomatis mengubahnya menjadi JSON Array.
        return response()->json($bookings);
    }
}