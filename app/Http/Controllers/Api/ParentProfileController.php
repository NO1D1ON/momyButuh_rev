<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; // Import model User

class ParentProfileController extends Controller
{
    /**
     * Menampilkan profil detail dari satu Orang Tua.
     */
    public function show(User $user)
    {
        // Cukup kembalikan data user yang ditemukan oleh Route Model Binding
        return response()->json($user);
    }
}