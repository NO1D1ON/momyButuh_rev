<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    /**
     * Mengambil dan menggabungkan riwayat top up dan booking.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil semua riwayat top-up milik pengguna
        $topUps = $user->topups()->where('status', 'success')->get()->map(function ($topup) {
            return [
                'type' => 'Top Up',
                'description' => 'Isi Ulang Saldo',
                'amount' => $topup->amount,
                'is_credit' => true, // Menandakan saldo masuk
                'date' => $topup->updated_at, // Gunakan updated_at karena saat itu transaksi disetujui
            ];
        });

        // 2. Ambil semua riwayat pembayaran booking milik pengguna
        $bookings = $user->bookings()->whereIn('status', ['confirmed', 'completed'])->get()->map(function ($booking) {
            return [
                'type' => 'Pembayaran Jasa',
                'description' => 'Pembayaran untuk ' . optional($booking->babysitter)->name,
                'amount' => $booking->total_price,
                'is_credit' => false, // Menandakan saldo keluar
                'date' => $booking->created_at,
            ];
        });

        // 3. Gabungkan kedua koleksi data
        $transactions = $topUps->concat($bookings);

        // 4. Urutkan semua transaksi berdasarkan tanggal, dari yang terbaru
        $sortedTransactions = $transactions->sortByDesc('date')->values();

        return response()->json($sortedTransactions);
    }
}