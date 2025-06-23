<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Topup;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        // Ambil data booking dengan paginasi
        $bookings = Booking::with(['user', 'babysitter'])->latest()->paginate(10, ['*'], 'bookings_page');

        // Ambil data topup dengan paginasi
        $topups = Topup::with('user')->latest()->paginate(10, ['*'], 'topups_page');

        return view('admin.transactions.index', compact('bookings', 'topups'));
    }
}