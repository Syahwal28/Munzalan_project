<?php

namespace App\Http\Controllers;

use App\Models\AsetModel;
use App\Exports\AsetExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AsetController extends Controller
{
    /**
     * Menampilkan daftar aset (Dikelompokkan per Kode Aset)
     */
    public function index()
    {
        // Mengambil data dan GROUP BY kode_aset agar di tabel tampil 1 baris per item
        // Kita ambil MAX(id) untuk keperluan tombol Edit/Hapus
        $assets = AsetModel::select('kode_aset', 'nama_barang', 'kategori', 'sumber_aset', 'lokasi', 'penanggung_jawab', 'satuan')
                    ->selectRaw('MAX(id) as id') 
                    ->selectRaw('SUM(jumlah) as total_stok') // Opsional: untuk debug query
                    ->groupBy('kode_aset', 'nama_barang', 'kategori', 'sumber_aset', 'lokasi', 'penanggung_jawab', 'satuan')
                    ->latest('created_at')
                    ->paginate(10);

        return view('aset.index', compact('assets'));
    }

    /**
     * Menampilkan form tambah data
     */
    public function create()
    {
        return view('aset.create');
    }

    /**
     * Menampilkan form edit
     * Mengambil satu aset sebagai sample data untuk ditampilkan di form.
     */
    public function edit($id)
    {
        // Cari aset berdasarkan ID
        $asset = AsetModel::findOrFail($id);
        
        // Kita juga perlu mengirim semua varian dengan kode yang sama ke view
        // agar bisa ditampilkan di tabel input dinamis (looping).
        // (Ini penting untuk fitur edit sekaligus yang baru kita buat)
        
        // Namun, di view edit yang baru, kita menggunakan logic:
        // $variants = \App\Models\AsetModel::where('kode_aset', $asset->kode_aset)->get();
        // Jadi cukup kirim $asset saja sudah cukup, karena view akan query sendiri (atau bisa kita query disini biar rapi).
        
        return view('aset.edit', compact('asset'));
    }

    /**
     * Menyimpan data baru ke database (Batch Input)
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_aset_utama'   => 'required', 
            'nama_barang'       => 'required|string',
            'kategori'          => 'required',
            'sumber_aset'       => 'required',
            'lokasi'            => 'required', // Lokasi tetap satu untuk semua (Global)
            
            'details'           => 'required|array',
            'details.*.jumlah'  => 'required|integer|min:1',
            'details.*.kondisi' => 'required',
            'details.*.penanggung_jawab' => 'required|string', // Validasi pindah ke sini
        ]);
        $prefix = $request->kode_aset_utama;
        $totalSaved = 0;

        // LOGIKA AUTO-INCREMENT (Mencari nomor terakhir di Database)
        // Kita cari kode yang depannya "AAA-"
        // Lalu kita urutkan angkanya dari yang terbesar untuk dapat nomor terakhir.
        $lastItem = AsetModel::where('kode_aset', 'LIKE', "{$prefix}-%")
                             ->selectRaw("kode_aset, CAST(SUBSTRING_INDEX(kode_aset, '-', -1) AS UNSIGNED) as nomor_urut")
                             ->orderBy('nomor_urut', 'desc')
                             ->first();

        // Tentukan angka start. Jika ada AAA-2, berarti start dari 3. Jika belum ada, start dari 1.
        $startNumber = 1;
        if ($lastItem) {
            $parts = explode('-', $lastItem->kode_aset);
            $lastNumber = (int) end($parts);
            $startNumber = $lastNumber + 1;
        }

            DB::transaction(function () use ($request, $prefix, $startNumber, &$totalSaved) {
            foreach ($request->details as $index => $detail) {
                
                // 3. GENERATE KODE BARU
                // Loop 1: StartNumber + 0 (Misal: AAA-3)
                // Loop 2: StartNumber + 1 (Misal: AAA-4)
                $currentNumber = $startNumber + $index;
                $kodeUnik = $prefix . '-' . $currentNumber;

                AsetModel::create([
                    'kode_aset'         => $kodeUnik,
                    'nama_barang'       => $request->nama_barang,
                    'kategori'          => $request->kategori,
                    'sumber_aset'       => $request->sumber_aset,
                    'lokasi'            => $request->lokasi, // Lokasi diambil dari Global
                    'tanggal_perolehan' => $request->tanggal_perolehan,
                    'harga_perolehan'   => $request->harga_perolehan,
                    
                    // --- DATA SPESIFIK PER BARIS ---
                    'jumlah'            => $detail['jumlah'],
                    'satuan'            => $detail['satuan'],
                    'kondisi'           => $detail['kondisi'],
                    'penanggung_jawab'  => $detail['penanggung_jawab'], // <--- Pindah ke sini
                    'keterangan'        => $detail['keterangan'] ?? $request->keterangan, 
                ]);
                
                $totalSaved++;
            }
        });

        return redirect()->route('assets.index')
                        ->with('success', "Berhasil! $totalSaved data aset tercatat.");
    }
    /**
     * Update data
     */
    public function update(Request $request, $id)
    {
        $assetMaster = AsetModel::findOrFail($id);
        $kodeAset = $assetMaster->kode_aset;

        // 1. Update Data Umum (YANG BENAR-BENAR GLOBAL SAJA)
        // Hapus 'penanggung_jawab' dari sini karena dia sekarang spesifik per baris
        AsetModel::where('kode_aset', $kodeAset)->update([
            'nama_barang'       => $request->nama_barang,
            'kategori'          => $request->kategori,
            'sumber_aset'       => $request->sumber_aset,
            'lokasi'            => $request->lokasi,
            'tanggal_perolehan' => $request->tanggal_perolehan,
            'harga_perolehan'   => $request->harga_perolehan,
        ]);

        // 2. Handle Detail Fisik (Update Existing & Create New)
        $submittedIds = collect($request->details)->pluck('id')->filter()->toArray();
        
        // Hapus data yang dihapus user di form
        AsetModel::where('kode_aset', $kodeAset)
                 ->whereNotIn('id', $submittedIds)
                 ->delete();

        foreach ($request->details as $detail) {
            
            // Siapkan data untuk update/create
            $dataToSave = [
                'jumlah'            => $detail['jumlah'],
                'satuan'            => $detail['satuan'],
                'kondisi'           => $detail['kondisi'],
                'penanggung_jawab'  => $detail['penanggung_jawab'], // AMBIL DARI DETAIL
                'keterangan'        => $detail['keterangan'] ?? null,
            ];

            if (isset($detail['id']) && $detail['id']) {
                // Update Data Lama
                AsetModel::where('id', $detail['id'])->update($dataToSave);
            } else {
                // Create Data Baru (Baris Tambahan saat Edit)
                // Kita perlu merge data global + data detail
                $newData = array_merge($dataToSave, [
                    'kode_aset'         => $kodeAset,
                    'nama_barang'       => $request->nama_barang,
                    'kategori'          => $request->kategori,
                    'sumber_aset'       => $request->sumber_aset,
                    'lokasi'            => $request->lokasi,
                    'tanggal_perolehan' => $request->tanggal_perolehan,
                    'harga_perolehan'   => $request->harga_perolehan,
                ]);
                
                AsetModel::create($newData);
            }
        }

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }

    /**
     * Menampilkan Detail Aset (Halaman yang dibuka saat Scan QR)
     */
    public function show($id)
    {
        // Cari aset berdasarkan ID
        $asset = AsetModel::findOrFail($id);
        
        // Tampilkan view detail
        return view('aset.show', compact('asset'));
    }

    /**
     * Hapus data
     */
    public function destroy(Request $request, $id)
    {
        // Cek apakah request hapus datang dari tombol "Hapus Group" di index
        // Kita cari aset berdasarkan ID dulu untuk dapat kodenya
        $asset = AsetModel::findOrFail($id);
        
        if ($request->has('hapus_by_kode')) {
            // Hapus SEMUA data yang punya Kode Aset sama (Baik & Rusak)
            AsetModel::where('kode_aset', $asset->kode_aset)->delete();
            $msg = 'Seluruh data aset dengan kode ' . $asset->kode_aset . ' berhasil dihapus.';
        } else {
            // Hapus SATU baris saja
            $asset->delete();
            $msg = 'Data varian aset berhasil dihapus.';
        }

        return redirect()->route('assets.index')->with('success', $msg);
    }

    /**
     * Export data aset ke format Excel.
     */
    public function exportExcel(Request $request)
    {
        $search = $request->get('search');
        
        // Menggunakan class Export khusus
        return Excel::download(new AsetExport($search), 'data_aset_munzalan.xlsx');
    }

    /**
     * Export data aset ke format PDF.
     */
    public function exportPdf(Request $request)
    {
        $search = $request->get('search');
        
        // Ambil data berdasarkan pencarian
        $query = AsetModel::select('kode_aset', 'nama_barang', 'kategori', 'lokasi', 'penanggung_jawab', 'jumlah', 'satuan')
                         ->groupBy('kode_aset', 'nama_barang', 'kategori', 'lokasi', 'penanggung_jawab', 'jumlah', 'satuan');
                         
        if ($search) {
            $query->where('nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('kode_aset', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%')
                  ->orWhere('lokasi', 'like', '%' . $search . '%');
        }
        
        $assets = $query->get();
        
        // Load view khusus untuk PDF
        $pdf = Pdf::loadView('aset.pdf', compact('assets', 'search'));
        
        return $pdf->download('data_aset_munzalan.pdf');
    }
}