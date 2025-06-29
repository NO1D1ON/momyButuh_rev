<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use App\Http\Resources\BabysitterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BabysitterAuthController extends Controller
{
    /**
     * Menampilkan semua babysitter yang tersedia
     */

    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari babysitter berdasarkan email
        $babysitter = Babysitter::where('email', $request->email)->first();

        // 3. Verifikasi babysitter dan password
        if (! $babysitter || ! Hash::check($request->password, $babysitter->password)) {
            // Jika otentikasi gagal, berikan respons error yang standar
            throw ValidationException::withMessages([
                'message' => ['Email atau password yang Anda masukkan salah.'],
            ]);
        }

        // 4. Jika berhasil, buat token Sanctum untuk user babysitter
        $token = $babysitter->createToken('babysitter-auth-token')->plainTextToken;

        // 5. Kembalikan respons sukses beserta token dan data user
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $babysitter // Mengirim data user yang login
        ], 200);
    }

    // Pastikan Anda juga punya method logout jika didefinisikan di routes
    public function logout(Request $request)
    {
        // Mencabut token yang digunakan untuk request ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

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
            
            $babysitters = $query->with('reviews.user:id,name') // Eager load relasi
                                 ->orderBy('rating', 'desc')
                                 ->orderBy('created_at', 'desc')
                                 ->paginate($perPage);

            return BabysitterResource::collection($babysitters);

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

            // PERBAIKAN: Binding parameter harus menyertakan SEMUA '?'
            // 3 untuk SELECT dan 4 untuk WHERE. Total 7 parameter.
            $bindings = [
                $latitude, $longitude, $latitude, // Untuk SELECT
                $latitude, $longitude, $latitude, $radius // Untuk WHERE
            ];

            // Query dengan Haversine Formula yang lebih robust
            $babysittersData = DB::table('babysitters')
                ->select([
                    'babysitters.*',
                    DB::raw('ROUND(
                        (6371 * acos(
                            LEAST(1.0, 
                                GREATEST(-1.0,
                                    cos(radians(?)) * cos(radians(COALESCE(latitude, 0))) * cos(radians(COALESCE(longitude, 0)) - radians(?)) + 
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
                                cos(radians(?)) * cos(radians(COALESCE(latitude, 0))) * cos(radians(COALESCE(longitude, 0)) - radians(?)) + 
                                sin(radians(?)) * sin(radians(COALESCE(latitude, 0)))
                            )
                        )
                    )) <= ?
                ', [$latitude, $longitude, $latitude, $radius]) // Binding lokal di sini juga bisa
                ->orderBy('distance', 'asc')
                ->orderBy('rating', 'desc')
                ->limit($limit)
                ->get();
            
            // Konversi hasil ke Collection of Babysitter models agar accessor (spt 'age') bisa jalan
            $babysitterModels = Babysitter::hydrate($babysittersData->toArray());

            return response()->json([
                'status' => 'success',
                'data' => BabysitterResource::collection($babysitterModels),
                'meta' => [
                    'total' => $babysitterModels->count(),
                    'radius' => $radius,
                    'center' => ['latitude' => $latitude, 'longitude' => $longitude]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in nearby babysitter search', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to search nearby babysitters', 'debug' => config('app.debug') ? ['error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()] : null], 500);
        }
    }

    // Fungsi lain seperti nearbyFast dan locationStats juga perlu perbaikan binding yang sama jika digunakan.

    public function search(Request $request)
    {
        $request->validate(['name' => 'required|string|min:2']);
        $keyword = $request->input('name');
        $babysitters = Babysitter::where('name', 'LIKE', "%{$keyword}%")
            ->where('is_available', true)
            ->limit(15)
            ->get();
        return BabysitterResource::collection($babysitters);
    }
}
