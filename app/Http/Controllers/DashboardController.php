<?php

namespace App\Http\Controllers;

use App\Models\AsetModel;
use App\Models\Transaksi;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Jenis Aset (Unique by Kode Aset)
        // Hanya menghitung barang yang stoknya > 0 (Barang Aktif)
        // Jika barang sudah dimusnahkan total (stok 0), tidak perlu dihitung sebagai aset aktif.
        $totalJenisAset = AsetModel::where('jumlah', '>', 0)
                                   ->distinct('kode_aset')
                                   ->count('kode_aset');

        // 2. Total Stok Fisik (Semua kondisi: Baik + Rusak, asalkan ada fisiknya)
        $totalStok = AsetModel::sum('jumlah');
        
        // 3. Statistik Kondisi (Berdasarkan Stok Unit, bukan Jumlah Baris Data)
        // Kita hitung unitnya, bukan barisnya, agar lebih real.
        $stokBaik = AsetModel::where('kondisi', 'Baik')->sum('jumlah');
        
        // Gabungkan Rusak Ringan & Berat jadi satu statistik "Rusak"
        $stokRusak = AsetModel::whereIn('kondisi', ['Rusak Ringan', 'Rusak Berat'])->sum('jumlah');

        // 4. Ambil 5 Transaksi Terakhir (Log Aktivitas)
        $transaksiTerbaru = Transaksi::with('aset')->latest('tanggal_keluar')->take(5)->get();

        return view('dashboard', compact(
            'totalJenisAset', 
            'totalStok', 
            'stokBaik', 
            'stokRusak', 
            'transaksiTerbaru'
        ));
    }
}