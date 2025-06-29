<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BabysitterResource; // <-- Pastikan ini di-import

class FavoriteController extends Controller
{
    /**
     * Menambah atau menghapus babysitter dari daftar favorit pengguna.
     */
    public function toggle(Request $request, $babysitterId)
    {
        $user = Auth::user();
        $result = $user->favorites()->toggle($babysitterId);

        if (count($result['attached']) > 0) {
            $message = 'Babysitter berhasil ditambahkan ke favorit.';
        } else {
            $message = 'Babysitter dihapus dari favorit.';
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Mengambil daftar DATA LENGKAP babysitter yang difavoritkan oleh pengguna.
     * VERSI INI SUDAH DIPERBAIKI.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil SEMUA DATA babysitter yang terkait melalui relasi 'favorites'
        $favoriteBabysitters = $user->favorites()->get();

        // Gunakan BabysitterResource untuk memformat hasilnya agar konsisten
        // dengan endpoint API lainnya.
        return BabysitterResource::collection($favoriteBabysitters);
    }
}