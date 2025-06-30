<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\BabysitterAvailability;
use Illuminate\Http\Request;

class BabysitterAvailabilityController extends Controller
{
    public function store(Request $request)
    {
        $babysitter = $request->user();
        $validated = $request->validate([
            'available_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'rate_per_hour' => 'required|integer|min:10000',
            'location_preference' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $babysitter->availabilities()->create($validated);

        return response()->json(['message' => 'Jadwal ketersediaan Anda berhasil dipublikasikan!'], 201);
    }
}