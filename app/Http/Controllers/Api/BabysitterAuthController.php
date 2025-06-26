<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Babysitter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class BabysitterAuthController extends Controller
{
    // Registrasi untuk Babysitter
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:babysitters',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string',
            'birth_date' => 'required|date',
            'address' => 'required|string',
        ]);

        $babysitter = Babysitter::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Jangan lupa hash password
            'phone_number' => $validated['phone_number'],
            'birth_date' => $validated['birth_date'],
            'address' => $validated['address'],
        ]);

        return response()->json(['message' => 'Registrasi babysitter berhasil.'], 201);
    }

    // Login untuk Babysitter
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari babysitter berdasarkan email
        $babysitter = Babysitter::where('email', $request->email)->first();

        // Validasi manual: Cek jika babysitter ada DAN passwordnya cocok
        if (!$babysitter || !Hash::check($request->password, $babysitter->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // Jika berhasil, buat token
        $token = $babysitter->createToken('auth_token_babysitter')->plainTextToken;

        return response()->json([
            'message' => 'Login babysitter berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $babysitter
        ]);
    }
}