<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\AsetModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        // Ambil data transaksi beserta relasi asetnya
        // Urutkan created_at descending (terbaru diatas)
        $transaksi = Transaksi::with('aset')->latest()->paginate(10);
        
        return view('transaksi.index', compact('transaksi'));
    }
    
    // Tampilkan Form Barang Keluar
    public function create()
    {
        // Kita butuh daftar aset untuk dipilih di dropdown
        $assets = AsetModel::where('jumlah', '>', 0)->get(); // Hanya aset yg ada stoknya
        return view('transaksi.keluar', compact('assets'));
    }

    // Simpan Transaksi & Kurangi Stok
    public function store(Request $request)
    {
        $request->validate([
            'aset_id'       => 'required|exists:tb_aset,id',
            'tanggal_keluar'=> 'required|date',
            'jumlah_keluar' => 'required|integer|min:1',
            'alasan'        => 'required|string',
            // Validasi Kondisional: Wajib isi harga JIKA alasan = Rusak/Hilang
            'biaya_tanggungan' => 'nullable|numeric|min:0', 
        ]);

        // Cek Stok Dulu
        $aset = AsetModel::findOrFail($request->aset_id);
        if ($aset->jumlah < $request->jumlah_keluar) {
            return back()->withInput()->withErrors(['jumlah_keluar' => 'Stok tidak cukup! Sisa stok: ' . $aset->jumlah]);
        }

        // Gunakan Database Transaction agar aman
        DB::transaction(function () use ($request, $aset) {
            
            // 1. Simpan Riwayat Transaksi
            Transaksi::create([
                'aset_id'           => $request->aset_id,
                'tanggal_keluar'    => $request->tanggal_keluar,
                'jumlah_keluar'     => $request->jumlah_keluar,
                'alasan'            => $request->alasan,
                'penerima'          => $request->penerima,
                // Pastikan biaya 0 jika bukan rusak
                'biaya_tanggungan'  => ($request->alasan == 'Rusak' || $request->alasan == 'Hilang') ? $request->biaya_tanggungan : 0,
                'keterangan'        => $request->keterangan
            ]);

            // 2. Kurangi Stok Aset Utama
            $aset->decrement('jumlah', $request->jumlah_keluar);
        });

        return redirect()->route('assets.index')->with('success', 'Transaksi barang keluar berhasil dicatat & stok berkurang.');
    }
}