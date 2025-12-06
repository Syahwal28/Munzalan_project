<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\PerbaikanController;
use App\Http\Controllers\TransaksiController;

/*
|--------------------------------------------------------------------------
| 1. ROUTE PUBLIK (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/

// Redirect root ke login
Route::get('/', [AuthController::class, 'showLoginForm']); 

// Halaman Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Halaman Detail Aset (Publik - Scan QR)
// PENTING: Tambahkan regex where('id', '[0-9]+') agar tidak bentrok dengan 'assets/create'
Route::get('assets/{id}', [AsetController::class, 'show'])
    ->name('assets.show')
    ->where('id', '[0-9]+');


/*
|--------------------------------------------------------------------------
| 2. ROUTE TERAUTENTIKASI (Wajib Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // =============================================================
    // ZONA "READ-ONLY" (Bisa dilihat oleh Admin, Ustad, Direktur)
    // =============================================================

    // A. DATA ASET
    Route::get('/assets', [AsetController::class, 'index'])->name('assets.index');
    Route::get('/assets/export/csv', [AsetController::class, 'exportCsv'])->name('assets.export.csv');
    Route::get('/assets/export/pdf', [AsetController::class, 'exportPdf'])->name('assets.export.pdf');

    // B. TRANSAKSI / ASET RUSAK
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');

    // C. PERBAIKAN
    Route::get('/perbaikan', [PerbaikanController::class, 'index'])->name('perbaikan.index');


    // =============================================================
    // ZONA KHUSUS ADMIN (Full Akses: Create, Edit, Delete)
    // =============================================================
    Route::middleware(['is_admin'])->group(function () {

        // A. KELOLA DATA ASET
        Route::get('/assets/create', [AsetController::class, 'create'])->name('assets.create');
        Route::post('/assets', [AsetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{id}/edit', [AsetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{id}', [AsetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{id}', [AsetController::class, 'destroy'])->name('assets.destroy');

        // B. KELOLA TRANSAKSI (Aset Rusak)
        Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');

        // C. KELOLA PERBAIKAN
        Route::get('/perbaikan/create', [PerbaikanController::class, 'create'])->name('perbaikan.create');
        Route::post('/perbaikan', [PerbaikanController::class, 'store'])->name('perbaikan.store');
        Route::put('/perbaikan/{id}', [PerbaikanController::class, 'update'])->name('perbaikan.update'); 
        Route::delete('/perbaikan/{id}', [PerbaikanController::class, 'destroy'])->name('perbaikan.destroy');

    });

});