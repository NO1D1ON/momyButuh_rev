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
    public function index(Request $request)
    {
        $babysitter = Auth::user();

        $bookings = $babysitter->bookings()
            // PERUBAHAN: Minta juga data 'address' dari relasi user
            ->with('user:id,name,address') 
            ->whereIn('status', ['confirmed', 'completed'])
            ->latest('booking_date')
            ->get();
        
        // Sekarang, Laravel akan secara otomatis menyertakan alamat user di setiap booking
        return response()->json($bookings);
    }
}