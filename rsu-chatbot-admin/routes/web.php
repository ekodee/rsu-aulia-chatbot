<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KnowledgeBaseController; // Tambahkan import ini
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', IsAdmin::class])->prefix('admin')->group(function () {

    // Dashboard (pakai data)
    Route::get('/dashboard', [KnowledgeBaseController::class, 'index'])
        ->name('admin.dashboard');

    // Scraper
    Route::get('/scraper', [KnowledgeBaseController::class, 'createScrap'])
        ->name('admin.scraper');

    Route::post('/scraper', [KnowledgeBaseController::class, 'store'])
        ->name('knowledge.store');

    Route::delete('/knowledge/{id}', [KnowledgeBaseController::class, 'destroy'])->name('knowledge.destroy');

    // PDF
    Route::get('/pdf', [KnowledgeBaseController::class, 'createPdf'])
        ->name('admin.pdf');

    Route::post('/pdf', [KnowledgeBaseController::class, 'storePdf'])
        ->name('knowledge.storePdf');

    // Users
    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
