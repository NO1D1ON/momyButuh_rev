<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingConfirmationController extends Controller
{
    public function parentConfirm(Request $request, Booking $booking)
    {
        // ... (kode otorisasi) ...

        $booking->parent_confirmed_at = now();
        $booking->save();

        // Panggil pengecekan dan langsung kembalikan hasilnya
        return $this->checkAndFinalizeBooking($booking);
    }

    public function babysitterConfirm(Request $request, Booking $booking)
    {
        // ... (kode otorisasi) ...
        
        $booking->babysitter_confirmed_at = now();
        $booking->save();

        // Panggil pengecekan dan langsung kembalikan hasilnya
        return $this->checkAndFinalizeBooking($booking);
    }

    /**
     * Logika untuk menyelesaikan booking dan MENGEMBALIKAN respons yang sesuai.
     */
    private function checkAndFinalizeBooking(Booking $booking)
    {
        // Jika KEDUA belah pihak sudah konfirmasi
        if ($booking->parent_confirmed_at && $booking->babysitter_confirmed_at) {
            
            DB::transaction(function () use ($booking) {
                $booking->status = 'completed';
                $booking->save();
                $booking->babysitter()->increment('balance', $booking->total_price);
            });

            // Kembalikan pesan SUKSES PENUH
            return response()->json([
                'status' => 'completed',
                'message' => 'Pesanan telah selesai dan pembayaran telah diproses!'
            ]);
        }

        // Jika BARU SALAH SATU yang konfirmasi
        return response()->json([
            'status' => 'pending_confirmation',
            'message' => 'Konfirmasi Anda berhasil. Menunggu persetujuan dari pihak lain.'
        ]);
    }
}