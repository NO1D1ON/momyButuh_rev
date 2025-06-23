<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Models\Babysitter;
use App\Models\Booking;
use Carbon\Carbon;

class BookingController extends Controller
{
    // Membuat booking baru
    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $parent = $request->user(); // Mengambil data Orang Tua yang sedang login

        $babysitter = Babysitter::findOrFail($validated['babysitter_id']);

        // Kalkulasi total harga (contoh sederhana)
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);
        $hours = $endTime->diffInHours($startTime);
        $totalPrice = $hours * $babysitter->rate_per_hour;

        // Pastikan saldo pengguna mencukupi
        if ($parent->balance < $totalPrice) {
            return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
        }

        // Kurangi saldo pengguna dan buat booking dalam satu transaksi
        try {
            \DB::transaction(function () use ($parent, $totalPrice, $validated, $babysitter) {
                // 1. Kurangi saldo
                $parent->decrement('balance', $totalPrice);

                // 2. Buat record booking
                Booking::create([
                    'user_id' => $parent->id,
                    'babysitter_id' => $babysitter->id,
                    'booking_date' => $validated['booking_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'total_price' => $totalPrice,
                    'status' => 'confirmed', // Langsung confirmed karena saldo dipotong
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat booking, silakan coba lagi.'], 500);
        }

        return response()->json(['message' => 'Booking berhasil dibuat.'], 201);
    }

    // Melihat riwayat booking milik pengguna yang login
    public function myBookings(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
                            ->with('babysitter:id,name,phone_number') // Hanya ambil data babysitter yang perlu
                            ->latest()
                            ->get();

        return response()->json($bookings);
    }
}