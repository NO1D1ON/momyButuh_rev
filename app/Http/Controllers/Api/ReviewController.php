<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $user = $request->user();
        $booking = Booking::findOrFail($validated['booking_id']);

        // Otorisasi: Pastikan yang memberi review adalah user yang memesan
        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak berhak memberi review untuk booking ini.'], 403);
        }

        // Pastikan booking sudah selesai
        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Booking belum selesai.'], 422);
        }
        
        // Pastikan booking belum pernah direview
        if (Review::where('booking_id', $booking->id)->exists()) {
            return response()->json(['message' => 'Booking ini sudah pernah Anda review.'], 422);
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'babysitter_id' => $booking->babysitter_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json(['message' => 'Terima kasih atas ulasan Anda.', 'data' => $review], 201);
    }
}