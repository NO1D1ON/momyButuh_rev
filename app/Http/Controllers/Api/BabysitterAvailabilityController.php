<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BabysitterAvailability;
use Illuminate\Http\Request;
use Carbon\Carbon; // TAMBAHKAN INI - Import Carbon yang hilang

class BabysitterAvailabilityController extends Controller
{
    public function index()
    {
        try {
            $availabilities = BabysitterAvailability::with('babysitter:id,name,rating,address,birth_date')
                ->where('available_date', '>=', now()->format('Y-m-d'))
                ->latest('created_at')
                ->get()
                ->map(function ($availability) {
                    $babysitter = $availability->babysitter;
                    
                    // Hitung umur dengan pengecekan yang lebih aman
                    $age = 0;
                    if ($babysitter && $babysitter->birth_date) {
                        try {
                            $birthDate = Carbon::parse($babysitter->birth_date);
                            $age = $birthDate->age;
                        } catch (\Exception $e) {
                            $age = 0;
                        }
                    }

                    return [
                        'id' => $availability->id,
                        'available_date' => $availability->available_date,
                        'start_time' => $availability->start_time,
                        'end_time' => $availability->end_time,
                        'rate_per_hour' => $availability->rate_per_hour,
                        'location_preference' => $availability->location_preference ?? 'Area sekitar',
                        // Sertakan data babysitter lengkap untuk model Flutter
                        'babysitter' => [
                            'id' => $babysitter->id ?? null,
                            'name' => $babysitter->name ?? 'Tanpa Nama',
                            'rating' => $babysitter->rating ?? 0.0,
                            'address' => $babysitter->address ?? null,
                            'photo_url' => null, // Set null karena kolom tidak ada
                            'birth_date' => $babysitter->birth_date ?? null,
                        ],
                        // Data yang dibutuhkan langsung oleh Flutter
                        'name' => $babysitter->name ?? 'Tanpa Nama',
                        'photo_url' => null, // Set null karena kolom tidak ada,
                        'rating' => (float) ($babysitter->rating ?? 0.0),
                        'age' => $age,
                    ];
                });

            return response()->json($availabilities);
            
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('BabysitterAvailability index error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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

            return response()->json([
                'message' => 'Jadwal ketersediaan Anda berhasil dipublikasikan!'
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('BabysitterAvailability store error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Gagal menyimpan jadwal',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}