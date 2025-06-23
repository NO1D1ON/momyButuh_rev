<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        // Ambil data booking, beserta data relasi user dan babysitter
        // 'with()' digunakan untuk Eager Loading agar query lebih efisien
        $bookings = Booking::with(['user', 'babysitter'])->latest()->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }
}