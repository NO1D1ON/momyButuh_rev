<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction; // Pastikan model Transaction di-import
use App\Http\Requests\Api\StoreBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Import trait otorisasi
use Carbon\Carbon;

class BookingController extends Controller
{
    use AuthorizesRequests; // Gunakan trait untuk metode authorize()

    /**
     * Menyimpan booking baru dari aplikasi mobile.
     */
    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $parent = $request->user();
        $babysitter = \App\Models\Babysitter::findOrFail($validated['babysitter_id']);

        $startTime = Carbon::parse($validated['booking_date'] . ' ' . $validated['start_time']);
        $endTime = Carbon::parse($validated['booking_date'] . ' ' . $validated['end_time']);

        // Cek jadwal yang tumpang tindih
        $existingBooking = Booking::where('babysitter_id', $babysitter->id)
            ->where('booking_date', $validated['booking_date'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime->toTimeString())
                      ->where('end_time', '>', $startTime->toTimeString());
                });
            })
            ->where('status', 'confirmed')
            ->exists();

        if ($existingBooking) {
            return response()->json(['message' => 'Jadwal babysitter pada tanggal dan jam tersebut sudah terisi.'], 409);
        }

        if ($startTime->greaterThanOrEqualTo($endTime)) {
            $endTime->addDay();
        }

        $minutes = $startTime->diffInMinutes($endTime);
        
        if ($minutes < 30) {
            return response()->json(['message' => 'Durasi booking minimal adalah 30 menit.'], 422);
        }

        // --- PERBAIKAN PERHITUNGAN HARGA ---
        $hours = ceil($minutes / 60);
        // Lakukan casting (int) untuk memastikan tipe data benar sebelum perkalian
        $totalPrice = (int) $hours * (int) $babysitter->rate_per_hour;
        
        if ((int) $parent->balance < $totalPrice) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
        }

        try {
            DB::transaction(function () use ($parent, $totalPrice, $validated, $babysitter) {
                // Kurangi saldo parent (saldo ditahan sementara oleh sistem)
                $parent->decrement('balance', $totalPrice);
                
                Booking::create([
                    'user_id' => $parent->id,
                    'babysitter_id' => $babysitter->id,
                    'booking_date' => $validated['booking_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'total_price' => $totalPrice,
                    'status' => 'confirmed', // Status awal, bisa juga 'pending' jika butuh approval babysitter
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat booking: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Booking berhasil dibuat.'], 201);
    }

    /**
     * Konfirmasi penyelesaian pekerjaan dari sisi orang tua.
     */
    public function parentConfirm(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking); // Membutuhkan BookingPolicy

        if ($booking->status !== 'confirmed') {
            return response()->json(['message' => 'Booking tidak dalam status yang bisa dikonfirmasi.'], 422);
        }

        $booking->parent_confirmed_at = now();
        $booking->save();

        if ($booking->babysitter_confirmed_at) {
            return $this->processBookingCompletion($booking);
        }

        return response()->json(['status' => 'pending_babysitter', 'message' => 'Konfirmasi berhasil. Menunggu konfirmasi dari babysitter.']);
    }

    /**
     * Konfirmasi penyelesaian pekerjaan dari sisi babysitter.
     */
    public function babysitterConfirm(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking); // Membutuhkan BookingPolicy

        if ($booking->status !== 'confirmed') {
            return response()->json(['message' => 'Booking tidak dalam status yang bisa dikonfirmasi.'], 422);
        }

        $booking->babysitter_confirmed_at = now();
        $booking->save();

        if ($booking->parent_confirmed_at) {
            return $this->processBookingCompletion($booking);
        }

        return response()->json(['status' => 'pending_parent', 'message' => 'Konfirmasi berhasil. Menunggu konfirmasi dari orang tua.']);
    }

    /**
     * Memproses penyelesaian booking, dan mentransfer saldo.
     * Metode ini dipanggil setelah kedua pihak melakukan konfirmasi.
     */
    protected function processBookingCompletion(Booking $booking)
    {
        if ($booking->status === 'completed') {
            return response()->json(['message' => 'Booking ini sudah selesai diproses.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($booking) {
                $babysitter = $booking->babysitter;
                
                // --- PERBAIKAN UTAMA: Casting tipe data sebelum operasi ---
                $paymentAmount = (int) $booking->total_price;

                // Tambahkan saldo ke babysitter
                $babysitter->increment('balance', $paymentAmount);

                // Catat transaksi untuk babysitter (payout)
                Transaction::create([
                    'babysitter_id' => $babysitter->id,
                    'type' => 'payout',
                    'amount' => $paymentAmount,
                    'description' => 'Pembayaran diterima untuk booking #' . $booking->id,
                    'is_credit' => true,
                ]);

                // Catat transaksi untuk parent (payment)
                Transaction::create([
                    'user_id' => $booking->user_id,
                    'type' => 'payment',
                    'amount' => $paymentAmount,
                    'description' => 'Pembayaran untuk booking #' . $booking->id,
                    'is_credit' => false,
                ]);

                // Update status booking menjadi selesai
                $booking->status = 'completed';
                $booking->save();

                return ['status' => 'completed', 'message' => 'Booking telah selesai dan pembayaran berhasil diproses.'];
            });

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pembayaran: ' . $e->getMessage()], 500);
        }
    }
    
    // Metode myBookings dan accept tetap sama, bisa Anda tambahkan kembali di sini.
}