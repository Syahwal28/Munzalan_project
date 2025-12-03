<?php

namespace App\Http\Controllers;

use App\Models\AsetModel;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung Statistik
        $totalAset = AsetModel::count();
        $totalStok = AsetModel::sum('jumlah');
        
        // Hitung Aset Kondisi Baik vs Rusak
        $asetBaik = AsetModel::where('kondisi', 'Baik')->count();
        $asetRusak = AsetModel::whereIn('kondisi', ['Rusak Ringan', 'Rusak Berat'])->count();

        // Ambil 5 Transaksi Terakhir untuk Widget
        $transaksiTerbaru = Transaksi::with('aset')->latest()->take(5)->get();

        return view('dashboard', compact('totalAset', 'totalStok', 'asetBaik', 'asetRusak', 'transaksiTerbaru'));
    }
}