<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTopupRequest;
use App\Models\Topup;
use Illuminate\Http\Request;

class TopupController extends Controller
{
    // Mengajukan permintaan Top Up baru
    public function store(StoreTopupRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        // Handle file upload
        $path = null;
        if ($request->hasFile('payment_proof')) {
            // Simpan file di dalam folder 'storage/app/public/proofs'
            $path = $request->file('payment_proof')->store('proofs', 'public');
        }

        // Buat record top up baru
        $topup = Topup::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'payment_proof' => $path,
            'status' => 'pending', // Status awal selalu pending
        ]);

        return response()->json([
            'message' => 'Permintaan top up berhasil diajukan dan sedang diproses.',
            'data' => $topup
        ], 201);
    }

    // Melihat riwayat top up milik pengguna yang login
    public function index(Request $request)
    {
        $topups = $request->user()->topups()->latest()->get();
        return response()->json($topups);
    }
}