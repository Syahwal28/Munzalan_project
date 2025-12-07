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
    public function index(Request $request)
    {
        // 1. QUERY DASAR
        $query = Perbaikan::with('aset');

        // 2. FILTER LOGIC
        if ($request->filled('aset_id')) {
            $query->where('aset_id', $request->aset_id);
        }

        if ($request->filled('penanggung_jawab')) {
            $query->where('penanggung_jawab', $request->penanggung_jawab);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tgl_masuk')) {
            $query->whereDate('tgl_masuk', $request->tgl_masuk);
        }

        // 3. EKSEKUSI QUERY
        $perbaikan = $query->latest('tgl_masuk')->paginate(10)->withQueryString();

        // 4. DATA UNTUK DROPDOWN
        // Ambil aset yang pernah masuk servis saja (dari tabel perbaikan) agar list tidak penuh sampah
        $idAsetServis = Perbaikan::distinct()->pluck('aset_id');
        $dataAset = AsetModel::withTrashed()
                                ->whereIn('id', $idAsetServis)
                                ->select('id', 'kode_aset', 'nama_barang')
                                ->orderBy('nama_barang')
                                ->get();

        // Ambil PJ Service unik
        $dataPJ = Perbaikan::distinct()->pluck('penanggung_jawab');

        return view('perbaikan.index', compact('perbaikan', 'dataAset', 'dataPJ'));
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

    // Simpan Data Perbaikan Baru
    public function store(Request $request)
    {
        $request->validate([
            'aset_id' => 'required|exists:tb_aset,id',
            'jumlah_perbaikan' => 'required|integer|min:1',
            'tgl_masuk' => 'required|date',
            'penanggung_jawab' => 'required|string',
            'keterangan_kerusakan' => 'required|string',
        ]);

        $cekAset = AsetModel::find($request->aset_id);
        
        // Cek apakah stok cukup
        if($cekAset->jumlah < $request->jumlah_perbaikan) {
            return back()->withInput()->withErrors(['jumlah_perbaikan' => 'Stok fisik tidak cukup! Sisa: '.$cekAset->jumlah]);
        }

        // Cek Kondisi (Hanya barang rusak yang boleh diservis)
        if(!in_array($cekAset->kondisi, ['Rusak Ringan', 'Rusak Berat'])) {
            return back()->withErrors(['aset_id' => 'Barang dengan kondisi Baik tidak perlu diservis.']);
        }

        DB::transaction(function () use ($request) {
            // 1. Simpan Data Perbaikan
            Perbaikan::create([
                'aset_id' => $request->aset_id,
                'jumlah_perbaikan' => $request->jumlah_perbaikan,
                'tgl_masuk' => $request->tgl_masuk,
                'penanggung_jawab' => $request->penanggung_jawab,
                'keterangan_kerusakan' => $request->keterangan_kerusakan,
                'status' => 'Proses', 
                'biaya' => 0,
            ]);

            // [PERUBAHAN DISINI]
            // KITA TIDAK MENGURANGI STOK. 
            // Stok di database tetap utuh agar di Index Aset angkanya tidak berkurang.
            // $aset->decrement('jumlah'); <--- INI DIHAPUS
        });

        return redirect()->route('perbaikan.index')->with('success', 'Data perbaikan dicatat. Status barang kini dalam perbaikan (Stok tetap tercatat sebagai aset yayasan).');
    }

    // Update Status Jadi Selesai
    public function update(Request $request, $id)
    {
        $perbaikan = Perbaikan::findOrFail($id);

        $request->validate([
            'tgl_selesai'   => 'required|date',
            'biaya'         => 'required|numeric|min:0',
            'kondisi_akhir' => 'required|string', 
            'jumlah_gagal'  => 'nullable|integer|min:1|max:'.$perbaikan->jumlah_perbaikan,
            'bukti_nota'    => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $perbaikan) {
            
            // 1. Simpan Data Perbaikan & Nota
            if ($request->hasFile('bukti_nota')) {
                if ($perbaikan->bukti_nota) Storage::disk('public')->delete($perbaikan->bukti_nota);
                $perbaikan->bukti_nota = $request->file('bukti_nota')->store('nota_perbaikan', 'public');
            }

            // Hitung Matematika Stok
            $totalServis  = $perbaikan->jumlah_perbaikan; // Contoh: 5
            $unitGagal    = ($request->kondisi_akhir == 'Rusak Berat') ? ($request->jumlah_gagal ?? $totalServis) : 0; // Contoh: 2
            $unitBerhasil = $totalServis - $unitGagal; // Contoh: 3

            // Update Status Perbaikan
            $perbaikan->tgl_selesai = $request->tgl_selesai;
            $perbaikan->biaya = $request->biaya;
            $perbaikan->keterangan_perbaikan = $request->keterangan_perbaikan . " (Sukses: $unitBerhasil, Gagal: $unitGagal)";
            $perbaikan->status = 'Selesai';
            $perbaikan->save();

            // 2. LOGIKA PINDAH STOK (SPLIT)
            $asetAsal = AsetModel::findOrFail($perbaikan->aset_id); // Ini aset kondisi awal (misal: Rusak Ringan)

            // --- A. HANDLE YANG GAGAL (PINDAH KE RUSAK BERAT) ---
            if ($unitGagal > 0) {
                // 1. Kurangi stok dari asal
                $asetAsal->decrement('jumlah', $unitGagal);

                // 2. Masukkan ke aset 'Rusak Berat'
                $asetRusak = AsetModel::where('kode_aset', $asetAsal->kode_aset)
                                      ->where('kondisi', 'Rusak Berat')
                                      ->first();

                $idUntukLog = null;

                if ($asetRusak) {
                    $asetRusak->increment('jumlah', $unitGagal);
                    $idUntukLog = $asetRusak->id;
                } else {
                    // Buat baru jika belum ada
                    $newRusak = $asetAsal->replicate();
                    $newRusak->kondisi = 'Rusak Berat';
                    $newRusak->jumlah = $unitGagal;
                    $newRusak->push();
                    $idUntukLog = $newRusak->id;
                }

                // 3. Catat di Log Aset Rusak (Agar tercatat sejarahnya)
                Transaksi::create([
                    'aset_id'           => $idUntukLog,
                    'jenis_transaksi'   => 'Laporan Rusak',
                    'tanggal_keluar'    => $request->tgl_selesai,
                    'jumlah_keluar'     => $unitGagal,
                    'penerima'          => $perbaikan->penanggung_jawab . ' (Gagal Servis)',
                    'alasan'            => 'Gagal diperbaiki (Rusak Berat). Keterangan: ' . ($request->keterangan_perbaikan ?? '-'),
                    'biaya_tanggungan'  => 0,
                ]);
            }

            // --- B. HANDLE YANG BERHASIL (PINDAH KE BAIK) ---
            if ($unitBerhasil > 0) {
                // 1. Kurangi stok dari asal (Karena statusnya sudah bukan rusak ringan lagi)
                $asetAsal->decrement('jumlah', $unitBerhasil);

                // 2. Masukkan ke aset 'Baik'
                $asetBaik = AsetModel::where('kode_aset', $asetAsal->kode_aset)
                                     ->where('kondisi', 'Baik')
                                     ->first();

                if ($asetBaik) {
                    $asetBaik->increment('jumlah', $unitBerhasil);
                } else {
                    // Buat baru jika belum ada
                    $newBaik = $asetAsal->replicate();
                    $newBaik->kondisi = 'Baik';
                    $newBaik->jumlah = $unitBerhasil;
                    $newBaik->push();
                }
            }
        });

        return redirect()->route('perbaikan.index')
                         ->with('success', 'Perbaikan selesai. Stok berhasil dipecah sesuai kondisi akhir.');
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