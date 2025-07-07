<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // PERBAIKAN 1: Jadikan 'comment' opsional (nullable)
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string', // <-- Diubah dari 'required'
        ]);

        $booking = Booking::find($request->booking_id);
        $user = $request->user(); // Mengambil pengguna yang terotentikasi dari request

        // PERBAIKAN 2: Logika Otorisasi Disederhanakan dan Diperkuat
        // Cek langsung apakah ID pengguna yang login adalah pemilik booking.
        // Ini sudah cukup untuk memastikan hanya orang tua yang benar yang bisa memberi review.
        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak berhak memberi review untuk booking ini.'], 403);
        }

        // Cek apakah booking sudah selesai (praktik terbaik)
        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Anda hanya bisa memberi review untuk booking yang sudah selesai.'], 403);
        }

        // Cek apakah review untuk booking ini sudah ada untuk mencegah duplikat
        $existingReview = Review::where('booking_id', $booking->id)->first();
        if ($existingReview) {
            return response()->json(['message' => 'Anda sudah memberi review untuk booking ini.'], 409); // 409 Conflict
        }

        // Buat review. Kolom 'comment' akan null jika tidak dikirim dari frontend.
        $review = Review::create([
            'user_id' => $user->id,
            'babysitter_id' => $booking->babysitter_id,
            'booking_id' => $booking->id,
            'rating' => $request->rating,
            'comment' => $request->comment, // Akan null jika tidak ada
        ]);

        // Update rating rata-rata babysitter
        $babysitter = $booking->babysitter;
        $babysitter->rating = $babysitter->reviews()->avg('rating');
        $babysitter->save();

        return response()->json([
            'message' => 'Review berhasil dikirim!',
            'review' => $review
        ], 201);
    }
}
