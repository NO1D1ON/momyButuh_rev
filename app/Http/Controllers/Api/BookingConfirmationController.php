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
    public function parentConfirm(Request $request, Booking $booking)
    {
        if (Auth::id() !== $booking->user_id) {
            return response()->json(['message' => 'Aksi tidak diizinkan.'], 403);
        }

        $booking->parent_confirmed_at = now();
        $booking->save();

        return $this->checkAndFinalizeBooking($booking);
    }

    public function babysitterConfirm(Request $request, Booking $booking)
    {
        if (Auth::id() !== $booking->babysitter_id) {
            return response()->json(['message' => 'Aksi tidak diizinkan.'], 403);
        }

        $booking->babysitter_confirmed_at = now();
        $booking->save();

        return $this->checkAndFinalizeBooking($booking);
    }

    private function checkAndFinalizeBooking(Booking $booking)
    {
        if ($booking->parent_confirmed_at && $booking->babysitter_confirmed_at) {
            try {
                DB::transaction(function () use ($booking) {
                    $parent = $booking->user;
                    $babysitter = $booking->babysitter;

                    // Validasi harga yang disimpan
                    $finalPrice = $booking->total_price;

                    // Jika belum dihitung sebelumnya, hitung ulang sekarang
                    if (!$finalPrice || $finalPrice <= 0) {
                        $startTime = Carbon::parse($booking->start_time);
                        $endTime = Carbon::parse($booking->end_time);
                        $durationInMinutes = $startTime->diffInMinutes($endTime);

                        // Minimal 1 jam
                        $hours = ceil($durationInMinutes / 60);
                        if ($hours < 1) $hours = 1;

                        $finalPrice = $hours * (int) $babysitter->rate_per_hour;

                        // Simpan total_price yang baru
                        $booking->total_price = $finalPrice;
                    }

                    // Validasi saldo parent
                    if ((int) $parent->balance < $finalPrice) {
                        throw new \Exception('Saldo orang tua tidak mencukupi untuk membayar Rp ' . number_format($finalPrice, 0, ',', '.'));
                    }

                    // Proses pembayaran
                    $parent->decrement('balance', $finalPrice);
                    $babysitter->increment('balance', $finalPrice);

                    // Tandai booking sebagai selesai
                    $booking->status = 'completed';
                    $booking->save();

                    \Log::info('Booking selesai dan pembayaran berhasil.', [
                        'booking_id' => $booking->id,
                        'total_price' => $finalPrice,
                        'duration' => $hours . ' jam',
                        'rate_per_hour' => $babysitter->rate_per_hour,
                        'user_balance' => $parent->balance,
                        'babysitter_balance' => $babysitter->balance,
                    ]);
                });

                $booking->refresh();

                return response()->json([
                    'status' => 'completed',
                    'message' => 'Pesanan selesai. Pembayaran sebesar Rp ' . number_format($booking->total_price, 0, ',', '.') . ' berhasil diproses.',
                    'payment_details' => [
                        'amount_paid' => $booking->total_price,
                        'formatted_amount' => 'Rp ' . number_format($booking->total_price, 0, ',', '.'),
                        'booking_id' => $booking->id,
                        'completed_at' => now()->toDateTimeString()
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal menyelesaikan booking: ' . $e->getMessage()
                ], 422);
            }
        }

        return response()->json([
            'status' => 'pending_confirmation',
            'message' => 'Konfirmasi berhasil. Menunggu persetujuan pihak lainnya.'
        ]);
    }

    public function getPaymentDetails(Booking $booking)
    {
        if (Auth::id() !== $booking->user_id && Auth::id() !== $booking->babysitter_id) {
            return response()->json(['message' => 'Akses tidak diizinkan.'], 403);
        }

        $startTime = Carbon::parse($booking->start_time);
        $endTime = Carbon::parse($booking->end_time);
        $minutes = $startTime->diffInMinutes($endTime);
        $hours = ceil($minutes / 60);
        if ($hours < 1) $hours = 1;

        $babysitter = $booking->babysitter;
        $calculatedPrice = $hours * (int) $babysitter->rate_per_hour;
        $finalPrice = max($booking->total_price, $calculatedPrice);

        return response()->json([
            'booking_id' => $booking->id,
            'duration_hours' => $hours,
            'rate_per_hour' => $babysitter->rate_per_hour,
            'calculated_price' => $calculatedPrice,
            'stored_total_price' => $booking->total_price,
            'final_price' => $finalPrice,
            'formatted_final_price' => 'Rp ' . number_format($finalPrice, 0, ',', '.'),
            'status' => $booking->status
        ]);
    }
}
