<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Http\Requests\Api\StoreBookingRequest; // Gunakan Form Request jika ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Menyimpan booking baru dari aplikasi mobile.
     */
    public function store(StoreBookingRequest $request) // Menggunakan StoreBookingRequest untuk validasi
    {
        $validated = $request->validated();
        $parent = $request->user();

        $babysitter = \App\Models\Babysitter::findOrFail($validated['babysitter_id']);

        $startTime = \Carbon\Carbon::parse($validated['start_time']);
        $endTime = \Carbon\Carbon::parse($validated['end_time']);
        $hours = $endTime->diffInHours($startTime);
        $totalPrice = $hours * $babysitter->rate_per_hour;

        if ($parent->balance < $totalPrice) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
        }

        try {
            DB::transaction(function () use ($parent, $totalPrice, $validated, $babysitter) {
                $parent->decrement('balance', $totalPrice);

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
            // Jika ada error, kirim response 500 dengan pesan error
            return response()->json(['message' => 'Gagal membuat booking: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Booking berhasil dibuat.'], 201);
    }

    /**
     * Mengambil riwayat booking milik pengguna yang sedang login.
     * VERSI FINAL DAN AMAN
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
                'status' => $booking->status,
            ];
        });

        // Mengembalikan langsung sebuah list JSON [...]
        return response()->json($formattedBookings);
    }
}