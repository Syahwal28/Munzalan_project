<?php

namespace App\Http\Controllers;

use App\Models\AsetModel;
use App\Models\Perbaikan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerbaikanController extends Controller
{
    // Tampilkan Daftar Perbaikan
    public function index()
    {
        $perbaikan = Perbaikan::with('aset')->latest()->paginate(10);
        return view('perbaikan.index', compact('perbaikan'));
    }

    // Tampilkan Form Input Perbaikan Baru
    public function create()
    {
        // LOGIKA BARU:
        // 1. Stok harus ada (> 0)
        // 2. Kondisi harus 'Rusak Ringan' atau 'Rusak Berat'
        $assets = AsetModel::where('jumlah', '>', 0)
                           ->whereIn('kondisi', ['Rusak Ringan', 'Rusak Berat']) 
                           ->get();

        return view('perbaikan.create', compact('assets'));
    }

    // Simpan Data Perbaikan Baru (Status: Proses)
    public function store(Request $request)
    {
        $request->validate([
            'aset_id' => 'required|exists:tb_aset,id',
            'jumlah_perbaikan' => 'required|integer|min:1',
            'tgl_masuk' => 'required|date',
            'penanggung_jawab' => 'required|string',
            'keterangan_kerusakan' => 'required|string',
        ]);

        // VALIDASI TAMBAHAN (BACKEND SAFETY)
        // Mencegah user nakal yang mencoba servis barang 'Baik' lewat inspect element
        $cekAset = AsetModel::find($request->aset_id);
        if($cekAset->jumlah < $request->jumlah_perbaikan) {
         
            return back()->withInput()->withErrors(['jumlah_perbaikan' => 'Stok tidak cukup! Sisa: '.$cekAset->jumlah]);
        }
        // Cek Kondisi (Tetap pakai logika 'Rusak Only')
        if(!in_array($cekAset->kondisi, ['Rusak Ringan', 'Rusak Berat'])) {
            return back()->withErrors(['aset_id' => 'Barang dengan kondisi Baik tidak bisa diajukan service.']);
        }

        DB::transaction(function () use ($request) {
            // 1. Simpan Data Perbaikan
            Perbaikan::create([
                'aset_id' => $request->aset_id,
                'jumlah_perbaikan' => $request->jumlah_perbaikan,
                'tgl_masuk' => $request->tgl_masuk,
                'penanggung_jawab' => $request->penanggung_jawab,
                'keterangan_kerusakan' => $request->keterangan_kerusakan,
                'status' => 'Proses', // Default Proses
                'biaya' => 0,
            ]);

            // 2. Kurangi Stok Aset (Karena barang sedang diservis)
            $aset = AsetModel::findOrFail($request->aset_id);
            $aset->decrement('jumlah', $request->jumlah_perbaikan);
        });

        return redirect()->route('perbaikan.index')->with('success', 'Data perbaikan dicatat. Stok aset dikurangi sementara.');
    }

    // Update Status Jadi Selesai & Upload Nota
    public function update(Request $request, $id)
    {
        $perbaikan = Perbaikan::findOrFail($id);

        $request->validate([
            'tgl_selesai' => 'required|date',
            'biaya'       => 'required|numeric|min:0',
            'kondisi_akhir' => 'required|string', 
            'jumlah_gagal'  => 'nullable|integer|min:1|max:'.$perbaikan->jumlah_perbaikan,
            'bukti_nota'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::transaction(function () use ($request, $perbaikan) {
            
            // 1. Upload Nota
            if ($request->hasFile('bukti_nota')) {
                if ($perbaikan->bukti_nota) {
                    Storage::disk('public')->delete($perbaikan->bukti_nota);
                }
                $path = $request->file('bukti_nota')->store('nota_perbaikan', 'public');
                $perbaikan->bukti_nota = $path;
            }

            // 2. Update Data Perbaikan
            $perbaikan->tgl_selesai = $request->tgl_selesai;
            $perbaikan->biaya = $request->biaya;
            $perbaikan->keterangan_perbaikan = $request->keterangan_perbaikan;
            $perbaikan->status = 'Selesai';
            // Simpan hasil akhir di tabel perbaikan (opsional, jika kolom ada)
            // $perbaikan->hasil_akhir = $request->kondisi_akhir; 
            $perbaikan->save();
            
            // 2. LOGIKA STOK PINTAR
            $asetAsal = AsetModel::findOrFail($perbaikan->aset_id);
            $totalUnit = $perbaikan->jumlah_perbaikan; // Misal 5
            
            $unitGagal = $request->kondisi_akhir == 'Rusak Berat' ? ($request->jumlah_gagal ?? $totalUnit) : 0; // Misal 3
            $unitBerhasil = $totalUnit - $unitGagal; // 5 - 3 = 2

            // A. Handle Unit Berhasil (Kembali ke Stok Baik)
            if ($unitBerhasil > 0) {
                // Cari wadah aset yang kondisinya 'Baik' dengan kode sama
                $asetBaik = AsetModel::where('kode_aset', $asetAsal->kode_aset)->where('kondisi', 'Baik')->first();
                
                if ($asetBaik) {
                    $asetBaik->increment('jumlah', $unitBerhasil);
                } else {
                    // Buat baru jika belum ada
                    $newAset = $asetAsal->replicate();
                    $newAset->kondisi = 'Baik';
                    $newAset->jumlah = $unitBerhasil;
                    $newAset->push();
                }
            }

            // B. Handle Unit Gagal (Masuk ke Log Aset Rusak & Buat Stok Rusak Berat)
            if ($unitGagal > 0) {
                // 1. Cari wadah aset 'Rusak Berat'
                $asetRusak = AsetModel::where('kode_aset', $asetAsal->kode_aset)->where('kondisi', 'Rusak Berat')->first();
                
                if ($asetRusak) {
                    $asetRusak->increment('jumlah', $unitGagal);
                    $idAsetRusak = $asetRusak->id;
                } else {
                    $newRusak = $asetAsal->replicate();
                    $newRusak->kondisi = 'Rusak Berat';
                    $newRusak->jumlah = $unitGagal;
                    $newRusak->push();
                    $idAsetRusak = $newRusak->id;
                }

                // 2. Catat Otomatis ke Tabel Transaksi (Menu Aset Rusak)
                // Pastikan Anda sudah punya Model Transaksi
                Transaksi::create([
                    'aset_id'           => $idAsetRusak, // Link ke aset yang rusak berat tadi
                    'jenis_transaksi'   => 'Keluar',
                    'tanggal_keluar'    => $request->tgl_selesai,
                    'jumlah_keluar'     => $unitGagal,
                    'penerima'          => 'Sistem (Hasil Servis Gagal)',
                    'alasan'            => 'Gagal Service: ' . ($request->keterangan_perbaikan ?? 'Tidak dapat diperbaiki'),
                    'biaya_tanggungan'  => 0, // Atau ambil dari biaya servis jika mau dibebankan
                ]);
                
                // Opsional: Karena sudah dicatat "Keluar/Musnah" di tabel transaksi, 
                // apakah stok di aset rusak berat mau dikurangi lagi jadi 0?
                // Jika MENU Transaksi itu fungsinya "Log Pemusnahan", maka ya, kurangi lagi.
                // Jika MENU Transaksi cuma "Log", biarkan stoknya tetap ada sebagai 'Rusak Berat'.
                
                // ASUMSI SAYA: Menu Transaksi = Pemusnahan (Stok Hilang).
                // Maka kita kurangi lagi stoknya.
                $finalAset = AsetModel::find($idAsetRusak);
                $finalAset->decrement('jumlah', $unitGagal);
            }
        });

        return redirect()->route('perbaikan.index')
                        ->with('success', 'Perbaikan selesai. Stok telah disesuaikan berdasarkan kondisi akhir.');
    }

    // method destroy jika perlu menghapus data service
    public function destroy($id)
    {
        $perbaikan = Perbaikan::findOrFail($id);
        
        // Jika status masih proses, kembalikan stok sebelum dihapus
        if($perbaikan->status == 'Proses') {
             $aset = AsetModel::findOrFail($perbaikan->aset_id);
             $aset->increment('jumlah', 1);
        }
        
        if ($perbaikan->bukti_nota) {
            Storage::disk('public')->delete($perbaikan->bukti_nota);
        }
        
        $perbaikan->delete();
        return redirect()->route('perbaikan.index')->with('success', 'Data perbaikan dihapus.');
    }
}