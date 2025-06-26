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
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\BabysitterDashboardController;
use App\Http\Controllers\Api\JobOfferController;
// use App\Http\Controllers\Api\Request;


// Rute Publik (tidak perlu login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/babysitters', [BabysitterController::class, 'index']);
Route::get('/babysitters/{babysitter}', [BabysitterController::class, 'show']);
Route::get('/babysitters/nearby', [BabysitterController::class, 'nearby']);

// Rute Otentikasi Babysitter
Route::post('/babysitter/register', [BabysitterAuthController::class, 'register']);
Route::post('/babysitter/login', [BabysitterAuthController::class, 'login']);

// Rute Terlindungi (butuh token login)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    // Nanti kita akan tambahkan rute booking, chat, dll di sini

    // Rute Booking BARU
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    // Rute Top Up BARU
    Route::post('/topups', [TopupController::class, 'store']);
    Route::get('/my-topups', [TopupController::class, 'index']);

    // Rute Chat BARU
    Route::get('/conversations', [MessageController::class, 'index']);
    Route::get('/conversations/{conversation}', [MessageController::class, 'show']);
    Route::post('/messages', [MessageController::class, 'store']);

    Route::post('/reviews', [ReviewController::class, 'store']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{babysitter}', [FavoriteController::class, 'toggle']);
    Route::get('/conversation/with/{babysitterId}', [MessageController::class, 'getOrCreateConversation']);

    Route::get('/babysitter/dashboard', [BabysitterDashboardController::class, 'index']);

    Route::get('/job-offers', [JobOfferController::class, 'index']);


    Route::get('/job-offers', [JobOfferController::class, 'index']);
    Route::get('/job-offers/{jobOffer}', [JobOfferController::class, 'show'])->middleware('auth:sanctum'); // <-- TAMBAHKAN RUTE INI

    Route::post('/job-offers', [JobOfferController::class, 'store']);

    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete']);

    Route::get('/transactions', [TransactionHistoryController::class, 'index']);

    Route::get('/babysitters/search', [BabysitterController::class, 'search']);
});
