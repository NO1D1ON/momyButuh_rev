<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;

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
        $validated['user_id'] = $request->user()->id;

        // Buat data baru di database
        $jobOffer = JobOffer::create($validated);

        return response()->json([
            'message' => 'Penawaran Anda berhasil dipublikasikan!',
            'data' => $jobOffer
        ], 201); // Status 201 Created
    }

    public function myOffers(Request $request)
    {
        $offers = JobOffer::where('user_id', $request->user()->id)
            ->latest()
            ->get();
            
        return response()->json($offers);
    }

    public function acceptOffer(Request $request, JobOffer $jobOffer)
    {
        // 2. Gunakan $request->user() untuk mendapatkan model yang terotentikasi via Sanctum
        $babysitter = $request->user();

        // Pastikan yang mengambil adalah Babysitter
        if (!$babysitter instanceof \App\Models\Babysitter) {
            return response()->json(['message' => 'Hanya babysitter yang bisa mengambil penawaran.'], 403);
        }

        // Pastikan penawaran masih terbuka
        if ($jobOffer->status !== 'open') {
            return response()->json(['message' => 'Penawaran ini sudah tidak tersedia.'], 409);
        }
        
        // (Opsional) Anda bisa menambahkan logika cek jadwal bentrok di sini

        try {
            DB::transaction(function () use ($jobOffer, $babysitter) {
                // Ubah status penawaran
                $jobOffer->status = 'taken';
                $jobOffer->save();

                // Buat record booking baru
                Booking::create([
                    'user_id' => $jobOffer->user_id,
                    'babysitter_id' => $babysitter->id,
                    'booking_date' => $jobOffer->job_date,
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