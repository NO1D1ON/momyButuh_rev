<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingConfirmationController extends Controller
{
    /**
     * Konfirmasi penyelesaian pekerjaan dari sisi Orang Tua.
     */
    public function parentConfirm(Request $request, Booking $booking)
    {
        if (Auth::id() !== $booking->user_id) {
            return response()->json(['message' => 'Aksi tidak diizinkan.'], 403);
        }

        $booking->parent_confirmed_at = now();
        $booking->save();

        return $this->checkAndFinalizeBooking($booking);
    }

    /**
     * Konfirmasi penyelesaian pekerjaan dari sisi Babysitter.
     */
    public function babysitterConfirm(Request $request, Booking $booking)
    {
        if (Auth::id() !== $booking->babysitter_id) {
            return response()->json(['message' => 'Aksi tidak diizinkan.'], 403);
        }
        
        $booking->babysitter_confirmed_at = now();
        $booking->save();

        return $this->checkAndFinalizeBooking($booking);
    }

    /**
     * Memeriksa status konfirmasi dan menyelesaikan booking jika kedua pihak setuju.
     * Logika diperbaiki untuk menghitung ulang harga dengan benar.
     */
    private function checkAndFinalizeBooking(Booking $booking)
    {
        if ($booking->parent_confirmed_at && $booking->babysitter_confirmed_at) {
            
            try {
                DB::transaction(function () use ($booking) {
                    $parent = $booking->user;
                    $babysitter = $booking->babysitter;

                    // --- PERBAIKAN UTAMA: KALKULASI ULANG HARGA ---
                    // 1. Hitung durasi kerja dalam jam dari data booking
                    $startTime = Carbon::parse($booking->start_time);
                    $endTime = Carbon::parse($booking->end_time);
                    $durationInHours = $endTime->diffInHours($startTime);
                    
                    // Jika durasi kurang dari 1 jam, bulatkan ke 1 jam
                    if ($durationInHours < 1) {
                        $durationInHours = 1;
                    }

                    // 2. Hitung total biaya berdasarkan harga per jam babysitter
                    $calculatedPrice = $durationInHours * $babysitter->price_per_hour;
                    
                    // 3. Tentukan harga yang akan digunakan untuk pembayaran
                    $finalPrice = $calculatedPrice;
                    
                    // Jika total_price sudah ada dan valid (> 0), gunakan yang lebih besar
                    // untuk menghindari kerugian
                    if ($booking->total_price > 0) {
                        $finalPrice = max($booking->total_price, $calculatedPrice);
                    }
                    
                    // Update total_price di booking dengan harga final
                    $booking->total_price = $finalPrice;
                    
                    // --- AKHIR PERBAIKAN ---

                    // 4. Validasi: Periksa apakah saldo orang tua mencukupi
                    if ($parent->balance < $finalPrice) {
                        throw new \Exception('Saldo orang tua tidak mencukupi untuk pembayaran sebesar Rp ' . number_format($finalPrice, 0, ',', '.'));
                    }

                    // 5. Kurangi saldo orang tua
                    $parent->decrement('balance', $finalPrice);

                    // 6. Tambah saldo babysitter
                    $babysitter->increment('balance', $finalPrice);

                    // 7. Ubah status booking menjadi 'completed'
                    $booking->status = 'completed';
                    $booking->save();
                    
                    // Log untuk debugging (opsional)
                    \Log::info('Booking completed', [
                        'booking_id' => $booking->id,
                        'duration_hours' => $durationInHours,
                        'price_per_hour' => $babysitter->price_per_hour,
                        'total_paid' => $finalPrice,
                        'parent_balance_remaining' => $parent->balance,
                        'babysitter_balance_new' => $babysitter->balance
                    ]);
                });

                // Refresh booking untuk mendapatkan data terbaru
                $booking->refresh();

                // Jika transaksi berhasil, kembalikan pesan sukses dengan nominal yang benar
                return response()->json([
                    'status' => 'completed',
                    'message' => 'Pesanan telah selesai dan pembayaran sebesar Rp ' . number_format($booking->total_price, 0, ',', '.') . ' telah berhasil diproses!',
                    'payment_details' => [
                        'amount_paid' => $booking->total_price,
                        'formatted_amount' => 'Rp ' . number_format($booking->total_price, 0, ',', '.'),
                        'booking_id' => $booking->id,
                        'completed_at' => now()->format('Y-m-d H:i:s')
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], 422);
            }
        }

        return response()->json([
            'status' => 'pending_confirmation',
            'message' => 'Konfirmasi Anda berhasil. Menunggu persetujuan dari pihak lain untuk menyelesaikan pembayaran.'
        ]);
    }
    
    /**
     * Method tambahan untuk mendapatkan detail pembayaran booking (opsional)
     */
    public function getPaymentDetails(Booking $booking)
    {
        // Pastikan user berhak mengakses booking ini
        if (Auth::id() !== $booking->user_id && Auth::id() !== $booking->babysitter_id) {
            return response()->json(['message' => 'Aksi tidak diizinkan.'], 403);
        }
        
        $startTime = Carbon::parse($booking->start_time);
        $endTime = Carbon::parse($booking->end_time);
        $durationInHours = $endTime->diffInHours($startTime);
        
        if ($durationInHours < 1) {
            $durationInHours = 1;
        }
        
        $babysitter = $booking->babysitter;
        $calculatedPrice = $durationInHours * $babysitter->price_per_hour;
        
        return response()->json([
            'booking_id' => $booking->id,
            'duration_hours' => $durationInHours,
            'price_per_hour' => $babysitter->price_per_hour,
            'calculated_price' => $calculatedPrice,
            'stored_total_price' => $booking->total_price,
            'final_price' => max($booking->total_price > 0 ? $booking->total_price : 0, $calculatedPrice),
            'formatted_final_price' => 'Rp ' . number_format(max($booking->total_price > 0 ? $booking->total_price : 0, $calculatedPrice), 0, ',', '.'),
            'status' => $booking->status
        ]);
    }
}