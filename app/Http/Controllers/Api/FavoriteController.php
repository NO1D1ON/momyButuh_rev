<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BabysitterResource;
use App\Models\User;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite status for a babysitter (add/remove).
     */
    public function toggle(Request $request, $babysitterId)
    {
        // Validasi agar hanya orang tua (User) yang bisa menambahkan favorit
        if (!($request->user() instanceof User)) {
            return response()->json(['message' => 'Hanya orang tua yang bisa menambahkan favorit.'], 403);
        }

        $user = $request->user();
        $result = $user->favorites()->toggle($babysitterId);

        $message = count($result['attached']) > 0
            ? 'Babysitter ditambahkan ke favorit.'
            : 'Babysitter dihapus dari favorit.';

        return response()->json(['message' => $message]);
    }

    /**
     * Get the list of favorite babysitters for the authenticated user.
     */
    public function index(Request $request)
    {
        // Validasi agar hanya orang tua yang bisa melihat daftar favorit
        if (!($request->user() instanceof User)) {
            return response()->json(['message' => 'Hanya orang tua yang bisa melihat favorit.'], 403);
        }

        // Ambil babysitter favorit beserta data availabilities
        $favoriteBabysitters = $request->user()
            ->favorites()
            ->with('availabilities')
            ->get();

        // Gunakan Resource agar struktur JSON rapi
        return BabysitterResource::collection($favoriteBabysitters);
    }

    /**
     * Get only the IDs of favorite babysitters.
     */
    public function getFavoriteIds(Request $request)
    {
        // Validasi agar hanya User (orang tua) yang bisa akses
        if (!($request->user() instanceof User)) {
            return response()->json([]);
        }

        $favoriteIds = $request->user()->favorites()->pluck('babysitter_id');

        return response()->json($favoriteIds);
    }
}
