<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BabysitterController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\MessageController; 
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BabysitterAuthController; 
use App\Http\Controllers\Api\BabysitterDashboardController;
use App\Http\Controllers\Api\JobOfferController;
use App\Http\Controllers\Api\TransactionHistoryController;
use App\Http\Controllers\Api\BabysitterBookingController;
use App\Http\Controllers\Api\ParentProfileController;
use App\Http\Controllers\Api\BookingConfirmationController;
use App\Http\Controllers\Api\LocationSearchController;
use App\Http\Controllers\Api\BabysitterAvailabilityController;
use App\Http\Controllers\Api\NotificationController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==================================================
// RUTE PUBLIK - Tidak memerlukan otentikasi
// ==================================================

// Otentikasi Pengguna (Orang Tua)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Otentikasi Babysitter
Route::post('/babysitter/register', [BabysitterAuthController::class, 'register']);
Route::post('/babysitter/login', [BabysitterAuthController::class, 'login']);

// Data Publik Babysitter
Route::get('/babysitters', [BabysitterController::class, 'index']);
Route::get('/babysitters/search', [BabysitterController::class, 'search']);
Route::get('/babysitters/nearby', [BabysitterController::class, 'nearby']);
Route::get('/babysitters/{babysitter}', [BabysitterController::class, 'show']);
Route::get('/job-offers', [JobOfferController::class, 'index']);
Route::get('/babysitter-availabilities', [BabysitterAvailabilityController::class, 'index']);



// ================================================================
// RUTE TERLINDUNGI - Memerlukan otentikasi User atau Babysitter
// ================================================================
// PERBAIKAN: Middleware diubah untuk secara eksplisit memeriksa guard 'sanctum' (untuk user) 
// dan 'babysitter'. Ini penting untuk otorisasi broadcasting.
Route::middleware(['auth:sanctum,babysitter'])->group(function () {

    // --- PROFIL & LOGOUT ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']); 
    // PERBAIKAN: Pastikan logout babysitter menggunakan controller-nya sendiri
    Route::post('/babysitter/logout', [BabysitterAuthController::class, 'logout']);



    // --- PERCAKAPAN & PESAN (CHAT) ---
    // Rute-rute ini telah kita kembangkan dan sekarang lengkap
    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::post('/conversations/initiate', [MessageController::class, 'initiateConversation']);
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'getMessages']);
    Route::post('/conversations/{conversation}/read', [MessageController::class, 'markAsRead']);
    Route::post('/conversations/{conversation}/typing', [MessageController::class, 'startTyping']);
    Route::post('/messages', [MessageController::class, 'store']);
    // PERBAIKAN: Rute '/conversation/with/{babysitterId}' telah dihapus karena redundan


    // --- FITUR UTAMA LAINNYA ---
    // Booking
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete']);

    // Penawaran Pekerjaan (Job Offers)
    Route::post('/job-offers', [JobOfferController::class, 'store']);
    Route::get('/job-offers/{jobOffer}', [JobOfferController::class, 'show']);

    // Favorit
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{babysitter}', [FavoriteController::class, 'toggle']);

    // Review
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Keuangan (Top Up & Transaksi)
    Route::post('/topups', [TopupController::class, 'store']);
    Route::get('/my-topups', [TopupController::class, 'index']);
    Route::get('/transactions', [TransactionHistoryController::class, 'index']);
    

    // --- RUTE KHUSUS BABYSITTER ---
    Route::get('/babysitter/dashboard', [BabysitterDashboardController::class, 'index']);

    Route::get('/babysitter/my-bookings', [BabysitterBookingController::class, 'index']);

    Route::get('/my-job-offers', [JobOfferController::class, 'myOffers']);

    Route::post('/job-offers/{jobOffer}/accept', [JobOfferController::class, 'acceptOffer']);

    Route::get('/parents/{user}', [ParentProfileController::class, 'show']);

    Route::get('/parent-profile/{user}', [ParentProfileController::class, 'show']);

    Route::post('/bookings/{booking}/parent-confirm', [BookingConfirmationController::class, 'parentConfirm']);
    Route::post('/bookings/{booking}/babysitter-confirm', [BookingConfirmationController::class, 'babysitterConfirm']);

    Route::get('/location/search', [LocationSearchController::class, 'search']);
    Route::get('/location/details', [LocationSearchController::class, 'getDetails']);
    Route::post('/babysitter/availabilities', [BabysitterAvailabilityController::class, 'store']);

    Route::post('/favorites/{babysitterId}/toggle', [FavoriteController::class, 'toggle']);

    Route::get('/babysitter-availabilities/nearby', [BabysitterAvailabilityController::class, 'getNearbyAvailabilities']);
    Route::get('/favorites/ids', [FavoriteController::class, 'getFavoriteIds']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);

    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
});
