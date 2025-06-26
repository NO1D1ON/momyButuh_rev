<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Menambah atau menghapus babysitter dari daftar favorit pengguna.
     */
    public function toggle(Request $request, $babysitterId)
    {
        $user = Auth::user();
        // `toggle` adalah fungsi bawaan Laravel untuk relasi many-to-many
        // yang sangat efisien untuk kasus ini.
        $result = $user->favorites()->toggle($babysitterId);

        // Cek hasil dari toggle untuk memberikan pesan yang sesuai
        if (count($result['attached']) > 0) {
            $message = 'Babysitter berhasil ditambahkan ke favorit.';
        } else {
            $message = 'Babysitter dihapus dari favorit.';
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Mengambil daftar ID babysitter yang difavoritkan oleh pengguna.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // `pluck` akan mengambil hanya kolom 'babysitter_id' dari tabel favorites
        $favoriteIds = $user->favorites()->pluck('babysitter_id');

        return response()->json($favoriteIds);
    }
}