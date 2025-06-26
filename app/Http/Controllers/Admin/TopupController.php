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
        // Pastikan hanya memproses topup yang statusnya 'pending'
        if ($topup->status !== 'pending') {
            return back()->with('error', 'Top up ini sudah pernah diproses sebelumnya.');
        }

        // Gunakan transaksi database untuk memastikan data konsisten.
        // Jika salah satu gagal, semua akan dibatalkan.
        DB::transaction(function () use ($topup) {
            // 1. Update status topup menjadi 'success'
            $topup->status = 'success';
            $topup->admin_notes = 'Disetujui oleh admin pada ' . now();
            $topup->save();

            // 2. Tambahkan saldo ke user yang bersangkutan
            // `increment` adalah cara aman untuk menambah nilai numerik.
            $topup->user()->increment('balance', $topup->amount);
        });

        return redirect()->route('topups.index')->with('success', 'Top up berhasil disetujui dan saldo pengguna telah ditambahkan.');
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