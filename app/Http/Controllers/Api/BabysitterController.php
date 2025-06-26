<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use App\Http\Resources\BabysitterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BabysitterController extends Controller
{
    /**
     * Menampilkan semua babysitter yang tersedia
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');
            
            $query = Babysitter::where('is_available', true);
            
            // Tambahkan pencarian jika ada
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('bio', 'like', "%{$search}%")
                      ->orWhere('specialization', 'like', "%{$search}%");
                });
            }
            
            $babysitters = $query->orderBy('rating', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => BabysitterResource::collection($babysitters),
                'meta' => [
                    'current_page' => $babysitters->currentPage(),
                    'total' => $babysitters->total(),
                    'per_page' => $babysitters->perPage(),
                    'last_page' => $babysitters->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching babysitters: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch babysitters',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu babysitter
     */
    public function show(Babysitter $babysitter)
    {
        try {
            // Load relasi yang diperlukan
            $babysitter->load([
                'reviews.user:id,name',
                'services',
                'availableSchedules'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => new BabysitterResource($babysitter)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching babysitter detail: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch babysitter details'
            ], 500);
        }
    }

    /**
     * Mencari babysitter terdekat menggunakan Haversine formula
     */
    public function nearby(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:1|max:100',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $latitude = $validated['latitude'];
            $longitude = $validated['longitude'];
            $radius = $validated['radius'] ?? 25; // Default radius 25 KM
            $limit = $validated['limit'] ?? 20;

            // Log untuk debugging
            Log::info('Nearby babysitter search', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius' => $radius
            ]);

            // Cek apakah ada babysitter dengan koordinat yang valid
            $hasLocationData = Babysitter::whereNotNull('latitude')
                                       ->whereNotNull('longitude')
                                       ->where('latitude', '!=', 0)
                                       ->where('longitude', '!=', 0)
                                       ->exists();

            if (!$hasLocationData) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No babysitters with location data found',
                    'data' => [],
                    'meta' => [
                        'total' => 0,
                        'radius' => $radius,
                        'center' => [
                            'latitude' => $latitude,
                            'longitude' => $longitude
                        ]
                    ]
                ]);
            }

            // Query dengan Haversine Formula yang lebih robust
            $babysitters = DB::table('babysitters')
                ->select([
                    'babysitters.*',
                    DB::raw('ROUND(
                        (6371 * acos(
                            LEAST(1.0, 
                                GREATEST(-1.0,
                                    cos(radians(?)) * cos(radians(COALESCE(latitude, 0))) * 
                                    cos(radians(COALESCE(longitude, 0)) - radians(?)) + 
                                    sin(radians(?)) * sin(radians(COALESCE(latitude, 0)))
                                )
                            )
                        )), 2
                    ) AS distance')
                ])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->where('is_available', true)
                ->whereRaw('
                    (6371 * acos(
                        LEAST(1.0, 
                            GREATEST(-1.0,
                                cos(radians(?)) * cos(radians(COALESCE(latitude, 0))) * 
                                cos(radians(COALESCE(longitude, 0)) - radians(?)) + 
                                sin(radians(?)) * sin(radians(COALESCE(latitude, 0)))
                            )
                        )
                    )) <= ?
                ', [$latitude, $longitude, $latitude, $radius])
                ->orderBy('distance', 'asc')
                ->orderBy('rating', 'desc')
                ->limit($limit)
                ->get();

            // Konversi hasil ke Collection of Babysitter models
            $babysitterModels = $babysitters->map(function ($item) {
                $babysitter = new Babysitter((array) $item);
                $babysitter->distance = $item->distance;
                return $babysitter;
            });

            return response()->json([
                'status' => 'success',
                'data' => BabysitterResource::collection($babysitterModels),
                'meta' => [
                    'total' => $babysitterModels->count(),
                    'radius' => $radius,
                    'center' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in nearby babysitter search', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search nearby babysitters',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ] : null
            ], 500);
        }
    }

    /**
     * Alternative nearby search menggunakan bounding box (lebih cepat)
     */
    public function nearbyFast(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:1|max:100',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $latitude = $validated['latitude'];
            $longitude = $validated['longitude'];
            $radius = $validated['radius'] ?? 25;
            $limit = $validated['limit'] ?? 20;

            // Hitung bounding box (approximation untuk performa lebih baik)
            $latitudeDelta = $radius / 111; // 1 derajat ≈ 111 km
            $longitudeDelta = $radius / (111 * cos(deg2rad($latitude)));

            $minLat = $latitude - $latitudeDelta;
            $maxLat = $latitude + $latitudeDelta;
            $minLng = $longitude - $longitudeDelta;
            $maxLng = $longitude + $longitudeDelta;

            $babysitters = Babysitter::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '>=', $minLat)
                ->where('latitude', '<=', $maxLat)
                ->where('longitude', '>=', $minLng)
                ->where('longitude', '<=', $maxLng)
                ->where('is_available', true)
                ->selectRaw('*, (
                    6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(latitude))
                    )
                ) as distance', [$latitude, $longitude, $latitude])
                ->having('distance', '<=', $radius)
                ->orderBy('distance')
                ->orderBy('rating', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => BabysitterResource::collection($babysitters),
                'meta' => [
                    'total' => $babysitters->count(),
                    'radius' => $radius,
                    'method' => 'bounding_box',
                    'center' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in fast nearby search: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search nearby babysitters'
            ], 500);
        }
    }

    /**
     * Mendapatkan statistik babysitter berdasarkan lokasi
     */
    public function locationStats(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:1|max:100'
            ]);

            $latitude = $validated['latitude'];
            $longitude = $validated['longitude'];
            $radius = $validated['radius'] ?? 25;

            $stats = [
                'total_babysitters' => Babysitter::count(),
                'available_babysitters' => Babysitter::where('is_available', true)->count(),
                'nearby_babysitters' => 0,
                'average_rating' => 0,
                'radius' => $radius
            ];

            // Hitung babysitter terdekat
            $nearbyCount = DB::table('babysitters')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('is_available', true)
                ->whereRaw('
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(latitude))
                    )) <= ?
                ', [$latitude, $longitude, $latitude, $radius])
                ->count();

            $stats['nearby_babysitters'] = $nearbyCount;

            // Hitung rating rata-rata di area tersebut
            $avgRating = DB::table('babysitters')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('is_available', true)
                ->whereRaw('
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(latitude))
                    )) <= ?
                ', [$latitude, $longitude, $latitude, $radius])
                ->avg('rating');

            $stats['average_rating'] = round($avgRating ?? 0, 2);

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting location stats: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get location statistics'
            ], 500);
        }
    }

     public function search(Request $request)
    {
        // 1. Validasi input: pastikan ada kata kunci pencarian
        $request->validate([
            'name' => 'required|string|min:2'
        ]);

        $keyword = $request->input('name');

        // 2. Lakukan pencarian di database
        $babysitters = Babysitter::where('name', 'LIKE', "%{$keyword}%")
            ->where('is_available', true) // Hanya cari yang tersedia
            ->limit(15) // Batasi hasil pencarian
            ->get();

        // 3. Kembalikan hasil menggunakan BabysitterResource
        return BabysitterResource::collection($babysitters);
    }
}