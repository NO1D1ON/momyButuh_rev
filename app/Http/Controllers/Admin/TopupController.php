<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopupController extends Controller
{
    public function index(Request $request)
    {
        $query = Topup::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $topups = $query->paginate(15);
        return view('admin.topups.index', compact('topups'));
    }

    public function approve(Topup $topup)
    {
        // Pastikan hanya proses topup yang pending
        if ($topup->status !== 'pending') {
            return back()->with('error', 'Top up ini sudah diproses sebelumnya.');
        }

        // Gunakan transaksi database untuk memastikan konsistensi data
        DB::transaction(function () use ($topup) {
            // 1. Update status topup menjadi 'success'
            $topup->update(['status' => 'success']);
            // 2. Tambahkan saldo ke user
            $topup->user->increment('balance', $topup->amount);
        });

        return redirect()->route('topups.index')->with('success', 'Top up berhasil disetujui.');
    }

    public function reject(Request $request, Topup $topup)
    {
        if ($topup->status !== 'pending') {
            return back()->with('error', 'Top up ini sudah diproses sebelumnya.');
        }

        $request->validate(['admin_notes' => 'required|string|max:255']);

        $topup->update([
            'status' => 'failed',
            'admin_notes' => $request->admin_notes
        ]);

        return redirect()->route('topups.index')->with('success', 'Top up telah ditolak.');
    }
}