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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// RUTE PUBLIK - Tidak memerlukan otentikasi
//==================================================

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


// RUTE TERLINDUNGI - Memerlukan otentikasi
//================================================================
// PERBAIKAN UTAMA: Gunakan middleware 'auth:sanctum' saja.
// Sanctum cukup pintar untuk memeriksa semua guard yang relevan
// (seperti 'web' dan 'babysitter') yang dikonfigurasi di config/auth.php.
Route::middleware('auth:sanctum')->group(function () {

    // --- PROFIL & LOGOUT ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
 
    Route::post('/logout', [AuthController::class, 'logout']); 
    Route::post('/babysitter/logout', [BabysitterAuthController::class, 'logout']);


    // --- PERCAKAPAN & PESAN (CHAT) ---
    // Rute-rute ini diasumsikan dapat diakses oleh kedua jenis pengguna
    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/conversation/with/{babysitterId}', [MessageController::class, 'getConversationWithBabysitter']);


    // --- FITUR UTAMA LAINNYA ---
    // Booking
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete']);

    // Penawaran Pekerjaan (Job Offers)
    Route::get('/job-offers', [JobOfferController::class, 'index']);
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
    Route::get('/babysitter/dashboard', [BabysitterDashboardController::class, 'index'])
        ->middleware('can:is_babysitter'); // Contoh penggunaan policy/gate untuk keamanan tambahan

});

