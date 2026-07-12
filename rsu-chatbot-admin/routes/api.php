<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatHistoryController;

// Rute Publik (Bisa diakses tanpa login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute Privat (Hanya bisa diakses kalau punya Token / sudah login)
Route::middleware('auth:sanctum')->group(function () {

    // Rute untuk Next.js mengecek data profil user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/chat-sessions', [ChatHistoryController::class, 'index']);
    Route::get('/chat-sessions/{id}', [ChatHistoryController::class, 'show']);
    Route::post('/chat-sessions', [ChatHistoryController::class, 'store']);
    Route::delete('/chat-sessions/{id}', [ChatHistoryController::class, 'destroy']);
});
