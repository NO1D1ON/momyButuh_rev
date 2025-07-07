<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User; // <-- Pastikan model User di-import
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
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $booking = Booking::find($request->booking_id);
        $user = Auth::user();

        // --- PERBAIKAN LOGIKA OTORISASI ---

        // 1. Pastikan yang login adalah instance dari User (Orang Tua)
        if (!($user instanceof User)) {
            return response()->json(['message' => 'Hanya pengguna (orang tua) yang dapat memberi review.'], 403);
        }

        // 2. Cek apakah ID pengguna yang login sama dengan user_id pada booking
        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak berhak memberi review untuk booking ini.'], 403);
        }

        // 3. Cek apakah booking sudah selesai (opsional tapi sangat direkomendasikan)
        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Anda hanya bisa memberi review untuk booking yang sudah selesai.'], 403);
        }

        // 4. Cek apakah review untuk booking ini sudah ada
        $existingReview = Review::where('booking_id', $booking->id)->first();
        if ($existingReview) {
            return response()->json(['message' => 'Anda sudah memberi review untuk booking ini.'], 409); // 409 Conflict
        }

        // Jika semua validasi lolos, buat review
        $review = Review::create([
            'user_id' => $user->id,
            'babysitter_id' => $booking->babysitter_id,
            'booking_id' => $booking->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
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
