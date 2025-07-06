<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Babysitter;
use App\Models\BabysitterAvailability;
use App\Models\Transaction;
use App\Models\User;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Pastikan Log di-import
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Exception;

class BookingController extends Controller
{
    use AuthorizesRequests;

    private function calculatePriceFromRate(float $rate, string $booking_date, string $start_time, string $end_time): float
    {
        $startTime = Carbon::parse($booking_date . ' ' . $start_time);
        $endTime = Carbon::parse($booking_date . ' ' . $end_time);

        if ($startTime->greaterThanOrEqualTo($endTime)) {
            $endTime->addDay();
        }

        $durationInMinutes = $startTime->diffInMinutes($endTime);
        $durationInHours = $durationInMinutes / 60;

        if ($durationInHours < 1) {
            $durationInHours = 1;
        }

        return round($durationInHours * $rate, 2);
    }

    private function buildSuccessResponse(string $message, $bookingData, User $user, int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'booking' => $bookingData,
            'user' => new UserResource($user->fresh()),
        ], $statusCode);
    }

    public function store(StoreBookingRequest $request)
    {
        // --- PENGECEKAN ID PENGGUNA UNTUK DIAGNOSIS ---
        // Baris ini akan mencatat ID pengguna yang sedang login ke file log.
        Log::info('BOOKING ATTEMPT: Permintaan booking diterima dari User ID: ' . $request->user()->id);
        // --- AKHIR DARI PENGECEKAN ---

        $validated = $request->validated();
        
        try {
            $availability = BabysitterAvailability::where('babysitter_id', $validated['babysitter_id'])
                ->where('available_date', $validated['booking_date'])
                ->where('start_time', '<=', $validated['start_time'])
                ->where('end_time', '>=', $validated['end_time'])
                ->firstOrFail();

            $ratePerHour = (float) $availability->rate_per_hour;
            if ($ratePerHour <= 0) {
                return response()->json(['message' => 'Tarif untuk jadwal ini tidak valid.'], 422);
            }

            $totalPrice = $this->calculatePriceFromRate($ratePerHour, $validated['booking_date'], $validated['start_time'], $validated['end_time']);
            
            $result = DB::transaction(function () use ($request, $validated, $totalPrice) {
                $parent = User::lockForUpdate()->findOrFail($request->user()->id);

                if ($parent->balance < $totalPrice) {
                    throw new Exception('Saldo Anda tidak mencukupi.');
                }

                $babysitter = Babysitter::findOrFail($validated['babysitter_id']);
                
                $parent->decrement('balance', $totalPrice);

                $booking = Booking::create([
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

                return ['booking' => $booking, 'parent' => $parent];
            });

            return $this->buildSuccessResponse(
                'Booking berhasil dibuat dan menunggu persetujuan babysitter.',
                $result['booking'],
                $result['parent'],
                201
            );

        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal membuat booking: ' . $e->getMessage()], 500);
        }
    }

    // ... (Sisa kode tidak perlu diubah) ...
    public function myBookings(Request $request)
    {
        $user = $request->user();
        $query = Booking::query();

        if ($user instanceof \App\Models\User) {
            $query->where('user_id', $user->id)->with('babysitter:id,name');
        } elseif ($user instanceof \App\Models\Babysitter) {
            $query->where('babysitter_id', $user->id)->with('user:id,name');
        }

        $bookings = $query->latest('booking_date')->get();

        $formattedBookings = $bookings->map(function ($booking) use ($user) {
            $isParent = $user instanceof \App\Models\User;
            $otherPartyName = $isParent 
                ? (optional($booking->babysitter)->name ?? 'Data Babysitter Dihapus')
                : (optional($booking->user)->name ?? 'Data Orang Tua Dihapus');

            return [
                'id' => $booking->id,
                'babysitter_name' => $isParent ? $otherPartyName : $user->name,
                'parent_name' => $isParent ? $user->name : $otherPartyName,
                'user_id' => $booking->user_id,
                'booking_date' => $booking->booking_date,
                'start_time' => $booking->start_time,
                'end_time' => $booking->end_time,
                'total_price' => $booking->total_price,
                'status' => $booking->status,
                'review' => $booking->review,
                'parent_confirmed_at' => $booking->parent_confirmed_at,
                'babysitter_confirmed_at' => $booking->babysitter_confirmed_at,
            ];
        });

        return response()->json($formattedBookings);
    }

    public function parentConfirm(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

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

    public function babysitterConfirm(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

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

    public function approve(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user instanceof \App\Models\Babysitter) {
            $booking->update(['babysitter_approved' => true]);
        } elseif ($user instanceof \App\Models\User) {
            $booking->update(['parent_approved' => true]);
        } else {
            return response()->json(['message' => 'Tipe pengguna tidak dikenali.'], 400);
        }

        $currentBookingState = $booking->fresh();

        if ($currentBookingState->parent_approved && $currentBookingState->babysitter_approved) {
            $currentBookingState->update(['status' => 'confirmed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil disetujui.',
            'data' => $currentBookingState,
        ]);
    }

    protected function processBookingCompletion(Booking $booking)
    {
        if ($booking->status === 'completed') {
            return response()->json(['message' => 'Booking ini sudah selesai diproses.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($booking) {
                $babysitter = Babysitter::lockForUpdate()->findOrFail($booking->babysitter_id);
                $parent = User::lockForUpdate()->findOrFail($booking->user_id);
                
                $paymentAmount = (float) $booking->total_price;
                $babysitter->increment('balance', $paymentAmount);

                Transaction::create([
                    'babysitter_id' => $babysitter->id,
                    'type' => 'payout',
                    'amount' => $paymentAmount,
                    'description' => 'Pembayaran diterima untuk booking #' . $booking->id,
                    'is_credit' => true,
                ]);

                Transaction::create([
                    'user_id' => $parent->id,
                    'type' => 'payment',
                    'amount' => $paymentAmount,
                    'description' => 'Pembayaran untuk booking #' . $booking->id,
                    'is_credit' => false,
                ]);

                $booking->status = 'completed';
                $booking->save();

                return [
                    'status' => 'completed', 
                    'message' => 'Booking telah selesai dan pembayaran berhasil diproses.',
                    'user' => new UserResource($parent->fresh())
                ];
            });

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pembayaran: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        $user = Auth::user();
        
        if (!($user instanceof Babysitter && $user->id === $booking->babysitter_id)) {
            return response()->json(['message' => 'Aksi tidak diizinkan.'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Booking ini tidak dapat ditolak lagi.'], 422);
        }

        try {
            $parent = DB::transaction(function () use ($booking) {
                $parentToUpdate = User::lockForUpdate()->findOrFail($booking->user_id);
                $parentToUpdate->increment('balance', (float)$booking->total_price);
                
                $booking->status = 'cancelled';
                $booking->save();

                return $parentToUpdate;
            });

            return $this->buildSuccessResponse(
                'Booking telah ditolak',
                $booking->fresh(),
                $parent
            );

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menolak booking: ' . $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk membatalkan booking ini.'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Booking ini tidak dapat dibatalkan lagi.'], 422);
        }
        
        try {
            $user = DB::transaction(function () use ($booking, $request) {
                $userToUpdate = User::lockForUpdate()->findOrFail($request->user()->id);
                $userToUpdate->increment('balance', (float)$booking->total_price);
                
                $booking->status = 'cancelled';
                $booking->save();

                return $userToUpdate;
            });

            return $this->buildSuccessResponse(
                'Booking berhasil dibatalkan dan saldo telah dikembalikan.',
                null, 
                $user
            );

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membatalkan booking: ' . $e->getMessage()], 500);
        }
    }
}
