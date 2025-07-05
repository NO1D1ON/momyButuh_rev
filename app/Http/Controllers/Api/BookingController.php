<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction; // Pastikan model Transaction di-import
use App\Http\Requests\Api\StoreBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Import trait otorisasi
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    use AuthorizesRequests; // Gunakan trait untuk metode authorize()

    /**
     * Menyimpan booking baru dari aplikasi mobile.
     */
    public function store(StoreBookingRequest $request)
{
    $validated = $request->validated();
    $parent = $request->user();

    // 1. Ambil data babysitter untuk mendapatkan rate per jam
    $babysitter = \App\Models\Babysitter::find($validated['babysitter_id']);
    if (!$babysitter) {
        return response()->json(['message' => 'Babysitter tidak ditemukan.'], 404);
    }

    // 2. Gabungkan tanggal dan jam ke format penuh
    $startTime = Carbon::parse($validated['booking_date'] . ' ' . $validated['start_time']);
    $endTime = Carbon::parse($validated['booking_date'] . ' ' . $validated['end_time']);

    // 3. Jika waktu selesai lebih awal dari mulai, berarti booking lewat tengah malam
    if ($startTime->greaterThanOrEqualTo($endTime)) {
        $endTime->addDay();
    }

    // 4. Validasi durasi minimal
    $durationInHours = $endTime->diffInHours($startTime);
    if ($durationInHours == 0) {
        $durationInHours = 1;
    }

    // 5. Cek tabrakan jadwal dengan booking confirmed
    $hasConflict = Booking::where('babysitter_id', $babysitter->id)
        ->where('booking_date', $validated['booking_date'])
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime->toTimeString())
                  ->where('end_time', '>', $startTime->toTimeString());
            });
        })
        ->where('status', 'confirmed')
        ->exists();

    if ($hasConflict) {
        return response()->json(['message' => 'Jadwal babysitter pada waktu tersebut sudah terisi.'], 409);
    }

    // 6. Hitung total harga
    $totalPrice = $durationInHours * (int) $babysitter->rate_per_hour;

    // 7. Cek saldo orang tua
    if ((int) $parent->balance < $totalPrice) {
        return response()->json(['message' => 'Saldo Anda tidak mencukupi.'], 422);
    }

    try {
        // 8. Simpan data booking dalam transaksi
        $booking = DB::transaction(function () use ($parent, $validated, $babysitter, $totalPrice) {
            // Tahan saldo
            $parent->decrement('balance', $totalPrice);

            // Buat booking dengan status awal
            return Booking::create([
                'user_id'             => $parent->id,
                'babysitter_id'       => $babysitter->id,
                'booking_date'        => $validated['booking_date'],
                'start_time'          => $validated['start_time'],
                'end_time'            => $validated['end_time'],
                'total_price'         => $totalPrice,
                'status'              => 'pending',
                'parent_approved'     => true,
                'babysitter_approved' => false,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibuat dan menunggu persetujuan babysitter.',
            'data' => $booking,
        ], 201);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal membuat booking: ' . $e->getMessage()], 500);
    }
}

    /**
     * --- METODE YANG DITAMBAHKAN KEMBALI ---
     * Mengambil riwayat booking milik pengguna yang sedang login.
     */
    public function myBookings(Request $request)
    {
        // Ambil booking berdasarkan user_id untuk orang tua atau babysitter_id untuk babysitter
        $user = $request->user();
        $query = Booking::query();

        if ($user instanceof \App\Models\User) {
            $query->where('user_id', $user->id)->with('babysitter:id,name');
        } elseif ($user instanceof \App\Models\Babysitter) {
            $query->where('babysitter_id', $user->id)->with('user:id,name');
        }

        $bookings = $query->latest('booking_date')->get();

        // Format data untuk dikirim ke frontend
        $formattedBookings = $bookings->map(function ($booking) use ($user) {
            $isParent = $user instanceof \App\Models\User;
            $otherPartyName = $isParent 
                ? (optional($booking->babysitter)->name ?? 'Data Babysitter Dihapus')
                : (optional($booking->user)->name ?? 'Data Orang Tua Dihapus');

            return [
                'id' => $booking->id,
                'babysitter_name' => $isParent ? $otherPartyName : $user->name, // Menyesuaikan field
                'parent_name' => $isParent ? $user->name : $otherPartyName, // Menyesuaikan field
                'user_id' => $booking->user_id,
                'booking_date' => $booking->booking_date,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'total_price' => $booking->total_price,
                'status' => $booking->status,
                'review' => $booking->review, // Sertakan review jika ada
                'parent_confirmed_at' => $booking->parent_confirmed_at,
                'babysitter_confirmed_at' => $booking->babysitter_confirmed_at,
            ];
        });

        return response()->json($formattedBookings);
    }

    /**
     * Konfirmasi penyelesaian pekerjaan dari sisi orang tua.
     */
    public function parentConfirm(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking); // Membutuhkan BookingPolicy

        if ($booking->status !== 'confirmed') {
            return response()->json(['message' => 'Booking tidak dalam status yang bisa dikonfirmasi.'], 422);
        }

        $booking->parent_confirmed_at = now();
        $booking->save();

        if ($booking->babysitter_confirmed_at) {
            return $this->processBookingCompletion($booking);
        }

        return response()->json(['status' => 'pending_babysitter', 'message' => 'Konfirmasi berhasil. Menunggu konfirmasi dari babysitter.']);
    }

    /**
     * Konfirmasi penyelesaian pekerjaan dari sisi babysitter.
     */
    public function babysitterConfirm(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking); // Membutuhkan BookingPolicy

        if ($booking->status !== 'confirmed') {
            return response()->json(['message' => 'Booking tidak dalam status yang bisa dikonfirmasi.'], 422);
        }

        $booking->babysitter_confirmed_at = now();
        $booking->save();

        if ($booking->parent_confirmed_at) {
            return $this->processBookingCompletion($booking);
        }

        return response()->json(['status' => 'pending_parent', 'message' => 'Konfirmasi berhasil. Menunggu konfirmasi dari orang tua.']);
    }

    /**
     * Memproses penyelesaian booking, dan mentransfer saldo.
     * Metode ini dipanggil setelah kedua pihak melakukan konfirmasi.
     */
    protected function processBookingCompletion(Booking $booking)
    {
        if ($booking->status === 'completed') {
            return response()->json(['message' => 'Booking ini sudah selesai diproses.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($booking) {
                $babysitter = $booking->babysitter;
                
                // --- PERBAIKAN UTAMA: Casting tipe data sebelum operasi ---
                $paymentAmount = (int) $booking->total_price;

                // Tambahkan saldo ke babysitter
                $babysitter->increment('balance', $paymentAmount);

                // Catat transaksi untuk babysitter (payout)
                Transaction::create([
                    'babysitter_id' => $babysitter->id,
                    'type' => 'payout',
                    'amount' => $paymentAmount,
                    'description' => 'Pembayaran diterima untuk booking #' . $booking->id,
                    'is_credit' => true,
                ]);

                // Catat transaksi untuk parent (payment)
                Transaction::create([
                    'user_id' => $booking->user_id,
                    'type' => 'payment',
                    'amount' => $paymentAmount,
                    'description' => 'Pembayaran untuk booking #' . $booking->id,
                    'is_credit' => false,
                ]);

                // Update status booking menjadi selesai
                $booking->status = 'completed';
                $booking->save();

                return ['status' => 'completed', 'message' => 'Booking telah selesai dan pembayaran berhasil diproses.'];
            });

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pembayaran: ' . $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, Booking $booking)
    {
        $user = Auth::user();

        // 1. Perbarui persetujuan berdasarkan tipe user
        if ($user instanceof \App\Models\Babysitter) {
            $booking->update(['babysitter_approved' => true]);
        } elseif ($user instanceof \App\Models\User) {
            $booking->update(['parent_approved' => true]);
        } else {
            return response()->json(['message' => 'Tipe pengguna tidak dikenali.'], 400);
        }

        // 2. Muat ulang state booking dari database untuk memastikan data valid
        $currentBookingState = $booking->fresh();

        // 3. Periksa apakah kedua pihak sudah setuju berdasarkan data terbaru
        if ($currentBookingState->parent_approved && $currentBookingState->babysitter_approved) {
            // Jika ya, ubah status menjadi 'confirmed'
            $currentBookingState->update(['status' => 'confirmed']);
        }

        // 4. Kembalikan data yang paling akhir
        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil disetujui.',
            'data' => $currentBookingState, // Mengembalikan state yang sudah divalidasi
        ]);
    }

    /**
     * Menolak sebuah booking.
     */
    public function reject(Request $request, Booking $booking)
    {
        // Hanya pihak yang relevan yang bisa menolak
        $user = Auth::user();
        if (!$user->tokenCan('is_parent') && !$user->tokenCan('is_babysitter')) {
             return response()->json(['message' => 'Aksi tidak diizinkan'], 403);
        }

        $booking->status = 'rejected';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking telah ditolak.',
            'data' => $booking,
        ]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $user = Auth::user();

        // Validasi:
        // 1. Hanya user yang membuat booking yang bisa membatalkan.
        // 2. Hanya bisa dibatalkan jika status masih 'pending'.
        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk membatalkan booking ini.'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Booking ini tidak dapat dibatalkan lagi.'], 422);
        }

        // Hapus booking dari database
        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan dan dihapus.'
        ], 200);
    }
}