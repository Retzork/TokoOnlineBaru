<?php

use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Notifikasi pembayaran Midtrans (Minggu 12). Tanpa login; keamanannya
// dijaga oleh verifikasi signature SHA-512 di OrderController::callback().
Route::post('/midtrans/callback', [OrderController::class, 'callback'])->name('midtrans.callback');
