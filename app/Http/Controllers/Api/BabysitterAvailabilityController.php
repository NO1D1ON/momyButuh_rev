<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BabysitterAvailability;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Pastikan DB facade di-import jika Anda menggunakannya

class BabysitterAvailabilityController extends Controller
{
    public function index()
    {
        try {
            // --- PERBAIKAN DI SINI: Hapus 'photo_url' dari with() ---
            $availabilities = BabysitterAvailability::with('babysitter:id,name,rating,address,birth_date')
                ->where('available_date', '>=', now()->format('Y-m-d'))
                ->latest('created_at')
                ->get()
                ->map(function ($availability) {
                    $babysitter = $availability->babysitter;
                    
                    $age = 0;
                    if ($babysitter && $babysitter->birth_date) {
                        try {
                            $birthDate = Carbon::parse($babysitter->birth_date);
                            $age = $birthDate->age;
                        } catch (\Exception $e) {
                            $age = 0;
                        }
                    }

                    // Logika ini sudah aman karena akan menangani jika photo_url tidak ada
                    return [
                        'id' => $availability->id,
                        'available_date' => $availability->available_date,
                        'start_time' => $availability->start_time,
                        'end_time' => $availability->end_time,
                        'rate_per_hour' => $availability->rate_per_hour,
                        'location_preference' => $availability->location_preference ?? 'Area sekitar',
                        'latitude' => $availability->latitude,
                        'longitude' => $availability->longitude,
                        'babysitter' => [
                            'id' => $babysitter->id ?? null,
                            'name' => $babysitter->name ?? 'Tanpa Nama',
                            'rating' => $babysitter->rating ?? 0.0,
                            'address' => $babysitter->address ?? null,
                            'photo_url' => $babysitter->photo_url ?? null, // Ini aman
                            'birth_date' => $babysitter->birth_date ?? null,
                        ],
                        'name' => $babysitter->name ?? 'Tanpa Nama',
                        'photo_url' => $babysitter->photo_url ?? null, // Ini aman
                        'rating' => (float) ($babysitter->rating ?? 0.0),
                        'age' => $age,
                    ];
                });

            return response()->json($availabilities);
            
        } catch (\Exception $e) {
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
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $babysitter->availabilities()->create($validated);

            return response()->json([
                'message' => 'Jadwal ketersediaan Anda berhasil dipublikasikan!'
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('BabysitterAvailability store error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Gagal menyimpan jadwal',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getNearbyAvailabilities(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $userLat = $request->latitude;
        $userLon = $request->longitude;
        $radius = 20;

        $haversine = "(
            6371 * acos(
                cos(radians(?))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?))
                * sin(radians(latitude))
            )
        )";

        $availabilities = BabysitterAvailability::with('babysitter:id,name,rating,photo_url,address,birth_date')
            ->selectRaw("*, {$haversine} AS distance", [$userLat, $userLon, $userLat])
            ->whereNotNull(['latitude', 'longitude']) // Pastikan hanya yang punya koordinat yang dicari
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->get();

        return response()->json($availabilities);
    }
}