<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BabysitterResource;
use App\Models\Babysitter;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // Melihat daftar favorit milik user yang login
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()->get();
        return BabysitterResource::collection($favorites);
    }

    // Menambah/menghapus favorit (toggle)
    public function toggle(Request $request, Babysitter $babysitter)
    {
        // Method toggle() akan otomatis menambah jika belum ada, dan menghapus jika sudah ada. Sangat efisien!
        $result = $request->user()->favorites()->toggle($babysitter->id);

        if (count($result['attached']) > 0) {
            $message = 'Babysitter berhasil ditambahkan ke favorit.';
        } else {
            $message = 'Babysitter berhasil dihapus dari favorit.';
        }

        return response()->json(['message' => $message]);
    }
}