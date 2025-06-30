<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TransactionHistoryController extends Controller
{
    /**
     * Mengambil dan menggabungkan riwayat top up dan booking.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $transactions = collect(); // Buat koleksi kosong untuk menampung transaksi

        // Cek jika pengguna yang login adalah Orang Tua
        if ($user instanceof User) {
            // 1. Ambil riwayat top-up milik Orang Tua
            $topUps = $user->topups()->where('status', 'success')->get()->map(function ($topup) {
                return [
                    'type' => 'Top Up Saldo',
                    'description' => 'Isi Ulang Saldo',
                    'amount' => $topup->amount,
                    'is_credit' => true, // Saldo masuk
                    'date' => $topup->updated_at,
                ];
            });

            // 2. Ambil riwayat pembayaran booking milik Orang Tua
            $bookings = $user->bookings()->whereIn('status', ['confirmed', 'completed'])->get()->map(function ($booking) {
                return [
                    'type' => 'Pembayaran Jasa',
                    'description' => 'Pembayaran untuk ' . optional($booking->babysitter)->name,
                    'amount' => $booking->total_price,
                    'is_credit' => false, // Saldo keluar
                    'date' => $booking->created_at,
                ];
            });

            // Gabungkan keduanya
            $transactions = $topUps->concat($bookings);

        } else {
            // Jika yang login adalah Babysitter
            // Ambil hanya booking yang sudah 'completed' karena saat itulah pembayaran diterima
            $bookings = $user->bookings()->where('status', 'completed')->get()->map(function ($booking) {
                return [
                    'type' => 'Penerimaan Dana',
                    'description' => 'Pembayaran dari ' . optional($booking->user)->name,
                    'amount' => $booking->total_price,
                    'is_credit' => true, // Saldo masuk
                    'date' => $booking->updated_at, // Gunakan updated_at saat status berubah menjadi completed
                ];
            });
            
            $transactions = $bookings;
        }

        // Urutkan semua transaksi berdasarkan tanggal, dari yang terbaru
        $sortedTransactions = $transactions->sortByDesc('date')->values();

        return response()->json($sortedTransactions);
    }
}