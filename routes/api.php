<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AdminController;

Route::get('/seats', [BookingController::class, 'index']);

Route::post('/bookings', [BookingController::class, 'store']);

// 1. Rute "Gerbang Masuk" (Publik)
// Alamat untuk Admin melakukan login
Route::post('/admin/login', [AdminController::class, 'login']);

// 2. Rute "Ruangan Rahasia" (Perlu Kunci / Login)
// 'middleware('auth:sanctum')' adalah "Gembok" dari Laravel.
Route::middleware('auth:sanctum')->group(function () {

    // Alamat untuk Admin mengambil SEMUA data pesanan
    Route::get('/admin/bookings', [AdminController::class, 'getBookings']);

    // Alamat untuk MENGONFIRMASI 1 pesanan (mengubah status)
    Route::put('/admin/bookings/{id}/confirm', [AdminController::class, 'confirmBooking']);

    // Alamat untuk MENGHAPUS 1 pesanan
    Route::delete('/admin/bookings/{id}', [AdminController::class, 'deleteBooking']);

    // Alamat untuk Admin logout
    Route::post('/admin/logout', [AdminController::class, 'logout']);

    // Alamat untuk mengecek siapa yang sedang login
    Route::get('/admin/user', [AdminController::class, 'getUser']);
});
