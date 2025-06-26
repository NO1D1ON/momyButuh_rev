<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Review;

class BabysitterDashboardController extends Controller
{
    /**
     * Mengambil data yang dibutuhkan untuk ditampilkan di dashboard babysitter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Mengambil data babysitter yang sedang login.
            // Kita gunakan Auth::user() karena guard default untuk API adalah 'sanctum'
            // dan kita akan login sebagai Babysitter.
            $babysitter = Auth::user();

            // Jika karena suatu alasan yang login bukan Babysitter, kembalikan error.
            if (!$babysitter instanceof \App\Models\Babysitter) {
                return response()->json(['message' => 'Akses tidak diizinkan.'], 403);
            }

            // 1. Mengambil booking yang masuk (statusnya 'confirmed') dan belum dimulai.
            $incomingBookings = Booking::where('babysitter_id', $babysitter->id)
                ->where('status', 'confirmed')
                ->where('booking_date', '>=', now()->format('Y-m-d'))
                ->with('user:id,name') // Ambil data ringkas dari orang tua
                ->orderBy('booking_date', 'asc')
                ->limit(5)
                ->get();

            // 2. Mengambil jadwal untuk hari ini.
            $todaySchedule = Booking::where('babysitter_id', $babysitter->id)
                ->where('booking_date', now()->format('Y-m-d'))
                ->whereIn('status', ['confirmed', 'completed'])
                ->orderBy('start_time', 'asc')
                ->get();

            // 3. Mengambil ulasan (review) terbaru yang diterima.
            $latestReview = Review::where('babysitter_id', $babysitter->id)
                ->with('user:id,name') // Ambil data ringkas dari pemberi ulasan
                ->latest() // Ambil yang paling baru
                ->first();

            // 4. Menyusun data untuk dikirim sebagai response JSON.
            $data = [
                'babysitter_name' => $babysitter->name,
                'incoming_bookings' => $incomingBookings,
                'today_schedule' => $todaySchedule,
                'latest_review' => $latestReview,
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            // Jika terjadi error, kirim response 500
            return response()->json([
                'message' => 'Gagal memuat data dashboard.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}