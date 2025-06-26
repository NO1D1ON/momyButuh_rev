<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\JobOffer;

class JobOfferController extends Controller
{
    // Method untuk menampilkan semua penawaran yang masih terbuka
    public function index()
    {
        $offers = JobOffer::where('status', 'open')
            ->with('user:id,name,address') // Ambil data ringkas orang tua
            ->latest()
            ->get();
        return response()->json($offers);
    }

    public function show(JobOffer $jobOffer)
    {
        // Muat relasi dengan data orang tua untuk ditampilkan di detail
        $jobOffer->load('user:id,name,address,phone_number');
        
        return response()->json($jobOffer);
    }

    public function store(Request $request)
    {
        // Validasi data yang masuk dari aplikasi Flutter
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'location_address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'job_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'offered_price' => 'required|integer|min:10000',
        ]);

        // Tambahkan user_id dari pengguna yang sedang login
        $validated['user_id'] = Auth::id();

        // Buat data baru di database
        $jobOffer = JobOffer::create($validated);

        return response()->json([
            'message' => 'Penawaran Anda berhasil dipublikasikan!',
            'data' => $jobOffer
        ], 201); // Status 201 Created
    }
}