<?php

use Illuminate\Support\Facades\Route;
// Panggil Controller yang BARU
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AsetController; 
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerbaikanController;
use App\Http\Controllers\TransaksiController;

// --- ROUTE TAMU (Bisa diakses tanpa login) ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Menampilkan data aset dari qr
Route::get('assets/{id}', [AsetController::class, 'show'])->name('assets.show');

// --- ROUTE KHUSUS MEMBER (Harus Login) ---
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Resource route tetap pakai nama 'assets' agar tidak perlu ubah view (route('assets.index'))
    // Tapi class-nya diarahkan ke AsetController
    Route::resource('assets', AsetController::class);

    
    Route::get('create', [AsetController::class, 'create'])->name('aset.create');
    // GROUP ROUTE BARANG KELUAR
    Route::prefix('barang-keluar')->name('transaksi.')->group(function() {
        // 1. Halaman Utama (Riwayat/Log) - Ini yang jadi menu sidebar
        Route::get('/', [TransaksiController::class, 'index'])->name('index'); 
        
        // 2. Halaman Input Form
        Route::get('/input', [TransaksiController::class, 'create'])->name('create'); 
        
        // 3. Proses Simpan
        Route::post('/store', [TransaksiController::class, 'store'])->name('store'); 
    });
    
    // gunakan resource controller, tapi method yg dipakai: index, create, store, update
    Route::resource('perbaikan', PerbaikanController::class);

    // Route untuk Export
    Route::get('export/excel', [AsetController::class, 'exportExcel'])->name('assets.export.excel');
    Route::get('export/pdf', [AsetController::class, 'exportPdf'])->name('assets.export.pdf');
});