<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Http\Requests\Api\StoreBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Menyimpan booking baru dari aplikasi mobile.
     */
    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $parent = $request->user();
        $babysitter = \App\Models\Babysitter::findOrFail($validated['babysitter_id']);

        // PERBAIKAN FINAL: Gabungkan tanggal + waktu untuk parsing akurat
        $startTime = Carbon::parse($validated['booking_date'] . ' ' . $validated['start_time']);
        $endTime = Carbon::parse($validated['booking_date'] . ' ' . $validated['end_time']);

        // VALIDASI & PENANGANAN KASUS LEWAT TENGAH MALAM
        if ($startTime->greaterThanOrEqualTo($endTime)) {
            // Jika end_time <= start_time dalam hari yang sama, asumsikan hari berikutnya
            $endTime->addDay();
        }

        // PERBAIKAN: Gunakan diffInMinutes dari startTime ke endTime (urutan benar)
        $minutes = $startTime->diffInMinutes($endTime);
        
        // VALIDASI DURASI MINIMAL
        if ($minutes < 30) {
            return response()->json([
                'message' => 'Durasi booking minimal adalah 30 menit.',
                'debug' => [
                    'start_time' => $startTime->toDateTimeString(),
                    'end_time' => $endTime->toDateTimeString(),
                    'minutes' => $minutes,
                    'input_start' => $validated['start_time'],
                    'input_end' => $validated['end_time']
                ]
            ], 422);
        }

        // Hitung total harga - bulatkan jam ke atas
        $hours = ceil($minutes / 60);
        $totalPrice = $hours * $babysitter->rate_per_hour;
        
        // Validasi saldo
        if ($parent->balance < $totalPrice) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
        }

        try {
            DB::transaction(function () use ($parent, $totalPrice, $validated, $babysitter) {
                // Kurangi saldo parent
                $parent->decrement('balance', $totalPrice);
                
                // Buat booking baru
                Booking::create([
                    'user_id' => $parent->id,
                    'babysitter_id' => $babysitter->id,
                    'booking_date' => $validated['booking_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'total_price' => $totalPrice,
                    'status' => 'confirmed',
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat booking: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Booking berhasil dibuat.',
            'data' => [
                'duration_minutes' => $minutes,
                'duration_hours_ceil' => $hours,
                'total_price' => $totalPrice,
                'babysitter_rate' => $babysitter->rate_per_hour
            ]
        ], 201);
    }

    /**
     * Mengambil riwayat booking milik pengguna yang sedang login.
     */
    public function myBookings(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
                            ->with('babysitter:id,name')
                            ->latest()
                            ->get();

        $formattedBookings = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'babysitter_name' => optional($booking->babysitter)->name ?? 'Data Babysitter Dihapus',
                'booking_date' => $booking->booking_date,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'total_price' => $booking->total_price,
                'status' => $booking->status,
            ];
        });

        return response()->json($formattedBookings);
    }
}