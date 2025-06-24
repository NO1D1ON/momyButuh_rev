<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use App\Http\Resources\BabysitterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BabysitterController extends Controller
{
    // Menampilkan semua babysitter yang tersedia
    public function index()
    {
        $babysitters = Babysitter::where('is_available', true)->get();
        return BabysitterResource::collection($babysitters);
    }

    // Menampilkan detail satu babysitter
    public function show(Babysitter $babysitter)
    {
        return new BabysitterResource($babysitter);
    }

    public function nearby(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|integer|min:1|max:100', // Radius dalam kilometer
        ]);

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];
        $radius = $validated['radius'] ?? 25; // Default radius 25 KM

        // Query Haversine Formula untuk menghitung jarak
        $babysitters = Babysitter::select('babysitters.*')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->where('is_available', true)
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->get();
        
        return BabysitterResource::collection($babysitters);
    }
}