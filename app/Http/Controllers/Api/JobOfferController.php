<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobOfferController extends Controller
{
    /**
     * Menampilkan semua penawaran yang masih terbuka.
     */
    public function index()
    {
        $offers = JobOffer::where('status', 'open')
            ->with('user:id,name,address') // Ambil data ringkas orang tua
            ->latest()
            ->get();
        return response()->json($offers);
    }

    /**
     * Menampilkan detail dari satu penawaran pekerjaan.
     */
    public function show(JobOffer $jobOffer)
    {
        $jobOffer->load('user:id,name,address,phone_number');
        return response()->json($jobOffer);
    }

    /**
     * Menyimpan penawaran pekerjaan baru dari Orang Tua.
     * VERSI INI SUDAH MENDUKUNG RENTANG TANGGAL.
     */
    public function store(Request $request)
    {
        // Validasi data yang masuk dari aplikasi Flutter
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'location_address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            // --- PERBAIKAN VALIDASI TANGGAL ---
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            // --- BATAS PERBAIKAN ---
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i', // Aturan 'after' tidak diperlukan jika bisa melewati tengah malam
            'offered_price' => 'required|integer|min:10000',
        ]);

        // Tambahkan user_id dari pengguna yang sedang login
        $validated['user_id'] = $request->user()->id;

        // Buat data baru di database
        $jobOffer = JobOffer::create($validated);

        return response()->json([
            'message' => 'Penawaran Anda berhasil dipublikasikan!',
            'data' => $jobOffer
        ], 201);
    }

    /**
     * Mengambil daftar penawaran yang dibuat oleh pengguna yang login.
     */
    public function myOffers(Request $request)
    {
        $offers = JobOffer::where('user_id', $request->user()->id)
            ->latest()
            ->get();
            
        return response()->json($offers);
    }

    /**
     * Aksi untuk Babysitter menerima penawaran pekerjaan.
     */
    public function acceptOffer(Request $request, JobOffer $jobOffer)
    {
        $babysitter = $request->user();
        $parent = $jobOffer->user; // Ambil data Orang Tua dari relasi penawaran

        // ... (kode validasi dan otorisasi yang sudah ada) ...

        // Validasi tambahan: Pastikan saldo Orang Tua mencukupi
        if ($parent->balance < $jobOffer->offered_price) {
            return response()->json(['message' => 'Pengguna yang membuat penawaran ini tidak memiliki saldo yang cukup.'], 422);
        }

        try {
            DB::transaction(function () use ($jobOffer, $babysitter, $parent) {
                // --- PERBAIKAN UTAMA DI SINI ---
                // 1. Kurangi saldo Orang Tua yang membuat penawaran
                $parent->decrement('balance', $jobOffer->offered_price);
                // --- BATAS PERBAIKAN ---
                
                // 2. Ubah status penawaran
                $jobOffer->status = 'taken';
                $jobOffer->save();

                // 3. Buat record booking baru
                Booking::create([
                    'user_id' => $jobOffer->user_id,
                    'babysitter_id' => $babysitter->id,
                    'booking_date' => $jobOffer->start_date,
                    'start_time' => $jobOffer->start_time,
                    'end_time' => $jobOffer->end_time,
                    'total_price' => $jobOffer->offered_price,
                    'status' => 'confirmed',
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil penawaran: ' . $e->getMessage()], 500);
        }
        
        return response()->json(['message' => 'Anda berhasil mengambil pekerjaan ini!']);
    }

}