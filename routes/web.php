<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BabysitterController; 
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\TopupController;
use App\Http\Controllers\Admin\TransactionController;

// Rute untuk pengguna yang BELUM login (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Rute untuk pengguna yang SUDAH login (auth)
Route::middleware('auth')->group(function () {
    
    // PERHATIKAN PERUBAHAN DI SINI
    Route::get('/dashboard', function () {
        // Arahkan ke view baru yang ada di dalam folder 'admin'
        return view('admin.dashboard'); 
    })->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::resource('babysitters', BabysitterController::class);

    Route::resource('users', UserController::class)->except(['create', 'store', 'show']);

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');

    Route::get('/topups', [TopupController::class, 'index'])->name('topups.index');
    Route::patch('/topups/{topup}/approve', [TopupController::class, 'approve'])->name('topups.approve');
    Route::patch('/topups/{topup}/reject', [TopupController::class, 'reject'])->name('topups.reject');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
});


// Jika pengguna membuka halaman utama, arahkan ke login jika belum login,
// atau ke dashboard jika sudah login.
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});