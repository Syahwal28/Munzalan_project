<?php

namespace App\Http\Controllers;

use App\Models\AsetModel;
use App\Models\Perbaikan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AsetController extends Controller
{
    /**
     * Menampilkan daftar aset (Dikelompokkan per Kode Aset)
     */
    public function index(Request $request)
    {
        // 1. QUERY DASAR (Persiapan Filter)
        $query = AsetModel::select('kode_aset', 'nama_barang', 'kategori', 'sumber_aset', 'lokasi', 'penanggung_jawab', 'satuan');

        // --- FILTER PENCARIAN (Search) ---
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_barang', 'like', '%'.$request->search.'%')
                  ->orWhere('kode_aset', 'like', '%'.$request->search.'%');
            });
        }

        // --- FILTER KATEGORI ---
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // FILTER PENANGGUNG JAWAB 
        if ($request->filled('penanggung_jawab')) {
            $query->where('penanggung_jawab', $request->penanggung_jawab);
        }

        // FILTER LOKASI
        if ($request->filled('lokasi')) {
            $query->where('lokasi', $request->lokasi);
        }

        // FILTER KONDISI 
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        // 2. EKSEKUSI QUERY (Grouping & Pagination)
        $assets = $query->selectRaw('MAX(id) as id') 
                        ->groupBy('kode_aset', 'nama_barang', 'kategori', 'sumber_aset', 'lokasi', 'penanggung_jawab', 'satuan')
                        ->orderByRaw('MAX(created_at) DESC') // Fix Strict Mode MySQL
                        ->paginate(10)
                        ->withQueryString(); // Agar filter tidak hilang saat klik halaman 2

        // 3. LOGIKA PERHITUNGAN (TRANSFORMASI DATA)
        // Kita suntikkan data tambahan ke dalam object assets, agar View tinggal cetak.
        $assets->getCollection()->transform(function ($item) {
            
            // Ambil semua varian data berdasarkan Kode Aset yang sama
            $allVariants = AsetModel::where('kode_aset', $item->kode_aset)->get();
            $variantIds  = $allVariants->pluck('id'); 

            // A. Hitung Stok Fisik (Database)
            // Total Stok = Jumlah semua kondisi (Baik + Rusak)
            $item->total_stok = $allVariants->sum('jumlah');
            
            // Rincian per Kondisi
            $item->stok_baik         = $allVariants->where('kondisi', 'Baik')->sum('jumlah');
            $item->stok_rusak_ringan = $allVariants->where('kondisi', 'Rusak Ringan')->sum('jumlah');
            $item->stok_rusak_berat  = $allVariants->where('kondisi', 'Rusak Berat')->sum('jumlah');

            // B. Hitung yang Sedang Diperbaiki (Service)
            // Mengambil dari tabel Perbaikan yang statusnya 'Proses'
            $item->sedang_diperbaiki = Perbaikan::whereIn('aset_id', $variantIds)
                                                ->where('status', 'Proses')
                                                ->sum('jumlah_perbaikan');

            // C. Hitung Total Aset Keseluruhan (Gudang + Service)
            // (Opsional: Jika ingin menampilkan total kepemilikan yayasan)
            // Karena logika kita sebelumnya stok TIDAK dikurangi saat servis, 
            // maka total_stok di atas sudah mencakup yang sedang diservis (jika stok fisik belum dikurangi).
            // Tapi jika stok fisik dikurangi, rumusnya: $item->total_stok + $item->sedang_diperbaiki.
            
            return $item;
        });
        $totalJenisAset = AsetModel::where('jumlah', '>', 0)
                                   ->distinct('kode_aset')
                                   ->count('kode_aset');
        // QUERY UNTUK DROPDOWN FILTER
        // Kita ambil data unik untuk mengisi opsi di Select2
        $dataLokasi = AsetModel::select('lokasi')->distinct()->pluck('lokasi');
        $dataKategori = AsetModel::select('kategori')->distinct()->pluck('kategori');
        $dataPJ     = AsetModel::select('penanggung_jawab')->distinct()->pluck('penanggung_jawab');
        $dataAsetList = AsetModel::select('kode_aset', 'nama_barang')
                                 ->distinct('kode_aset')
                                 ->orderBy('kode_aset')
                                 ->get();

        return view('aset.index', compact('assets', 'dataLokasi','dataKategori', 'dataPJ', 'dataAsetList','totalJenisAset'));
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
        // 1. Ambil DATA UTAMA (Single Object)
        // Ini digunakan untuk mengisi field: Nama, Kode, Kategori, Lokasi, PJ
        $asset = AsetModel::findOrFail($id);

        // 2. Ambil DATA VARIAN (Collection)
        // Ini digunakan untuk looping form: Jumlah & Kondisi
        $variants = AsetModel::where('kode_aset', $asset->kode_aset)
                           ->where('jumlah', '>', 0) // Filter stok > 0
                           ->orderBy('id')
                           ->get();

        // Kirim kedua variabel ke view
        return view('aset.edit', compact('asset', 'variants'));
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
                 ->where('jumlah', '>', 0)
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
        // Cari data aset berdasarkan ID dari QR Code
        $scannedAsset = AsetModel::findOrFail($id);

        // AMBIL DATA REALTIME (Berdasarkan Kode Aset)
        // Kita tidak hanya terpaku pada ID yang discan, tapi mencari 'saudara-saudaranya'
        // yang memiliki kode aset sama.
        $allVariants = AsetModel::where('kode_aset', $scannedAsset->kode_aset)->get();

        // 3. HITUNG STATISTIK REALTIME
        $summary = [
            'nama_barang'      => $scannedAsset->nama_barang, // Ambil nama dari yg discan
            'kode_aset'        => $scannedAsset->kode_aset,
            'kategori'         => $scannedAsset->kategori,
            'lokasi_utama'     => $scannedAsset->lokasi,
            'pj_utama'         => $scannedAsset->penanggung_jawab,
            
            // Hitung Stok
            'total_stok'       => $allVariants->sum('jumlah'),
            'stok_baik'        => $allVariants->where('kondisi', 'Baik')->sum('jumlah'),
            'stok_rusak_ringan'=> $allVariants->where('kondisi', 'Rusak Ringan')->sum('jumlah'),
            'stok_rusak_berat' => $allVariants->where('kondisi', 'Rusak Berat')->sum('jumlah'),
            
            // Cek Service
            'sedang_servis'    => Perbaikan::whereIn('aset_id', $allVariants->pluck('id'))
                                                       ->where('status', 'Proses')
                                                       ->sum('jumlah_perbaikan'),
        ];

        return view('aset.show', compact('scannedAsset', 'summary'));
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
    public function exportCsv(Request $request)
    {
        $search = $request->get('search');
        $fileName = 'data_aset_munzalan_' . now()->format('Ymd_His') . '.csv';

        // 1. Ambil Data Agregasi (Mirip dengan query di view Anda)
        $query = AsetModel::select('kode_aset', 'nama_barang', 'kategori', 'keterangan', 'sumber_aset', 
                                   'penanggung_jawab', 'lokasi', 'satuan', 'tanggal_perolehan', 'harga_perolehan')
                        ->selectRaw('SUM(jumlah) as total_stok')
                        ->selectRaw('SUM(CASE WHEN kondisi = "Baik" THEN jumlah ELSE 0 END) as stok_baik')
                        ->selectRaw('SUM(CASE WHEN kondisi = "Rusak Ringan" THEN jumlah ELSE 0 END) as stok_rusak_ringan')
                        ->selectRaw('SUM(CASE WHEN kondisi = "Rusak Berat" THEN jumlah ELSE 0 END) as stok_rusak_berat')
                        ->groupBy('kode_aset', 'nama_barang', 'kategori', 'keterangan', 'sumber_aset', 'penanggung_jawab', 
                                  'lokasi', 'satuan', 'tanggal_perolehan', 'harga_perolehan');
                        
        if ($search) {
            $query->where('nama_barang', 'like', '%' . $search . '%')
                ->orWhere('kode_aset', 'like', '%' . $search . '%')
                ->orWhere('kategori', 'like', '%' . $search . '%')
                ->orWhere('lokasi', 'like', '%' . $search . '%');
        }
        
        $assets = $query->get();

        // 2. Tentukan Headers HTTP
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // 3. Siapkan Kolom (Headings)
        $columns = [
            'NO INVENTARIS', 
            'NAMA ASET', 
            'KATEGORI', 
            'KETERANGAN/SPESIFIKASI', 
            'SUMBER', 
            'PJ', 
            'LOKASI', 
            'TOTAL STOK', 
            'BAIK', 
            'RUSAK RINGAN', 
            'RUSAK BERAT',
            'TANGGAL PEROLEHAN',
            'HARGA PEROLEHAN'
        ];
        
        // 4. Buat Callback untuk Streaming Data
        $callback = function() use ($assets, $columns) {
            $file = fopen('php://output', 'w');
            
            // Output Headings
            // Menggunakan semicolon (;) sebagai delimiter agar Excel Indonesia membaca format kolom dengan benar
            fputcsv($file, $columns, ';'); 
            
            foreach ($assets as $item) {
                fputcsv($file, [
                    $item->kode_aset,
                    $item->nama_barang,
                    $item->kategori,
                    $item->keterangan,
                    $item->sumber_aset,
                    $item->penanggung_jawab,
                    $item->lokasi,
                    $item->total_stok . ' ' . $item->satuan, // Total Stok
                    $item->stok_baik . ' ' . $item->satuan,
                    $item->stok_rusak_ringan . ' ' . $item->satuan,
                    $item->stok_rusak_berat . ' ' . $item->satuan,
                    $item->tanggal_perolehan,
                    $item->harga_perolehan,
                ], ';');
            }

            // --- TAMBAHKAN BARIS TOTAL KESELURUHAN (Sama seperti yang Anda minta) ---
            $grandTotalStok = $assets->sum('total_stok');
            $grandTotalBaik = $assets->sum('stok_baik');
            $grandTotalRusakRingan = $assets->sum('stok_rusak_ringan');
            $grandTotalRusakBerat = $assets->sum('stok_rusak_berat');
            
            // Baris Kosong
            fputcsv($file, [''], ';');

            // Baris Total
            fputcsv($file, [
                'TOTAL KESELURUHAN', // Kolom 1
                '', // Kolom 2
                '', // Kolom 3
                '', // Kolom 4
                '', // Kolom 5
                '', // Kolom 6 
                '', // Kolom 7 (Gabungan kolom di Excel)
                $grandTotalStok, 
                $grandTotalBaik, 
                $grandTotalRusakRingan, 
                $grandTotalRusakBerat,
            ], ';');

            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    /**
     * Export data aset ke format PDF.
     */
    public function exportPdf(Request $request)
    {
        $search = $request->get('search');
        
        // Ambil data berdasarkan pencarian
        $query = AsetModel::select('kode_aset', 'nama_barang', 'kategori', 'keterangan', 'lokasi', 
                                   'penanggung_jawab', 'jumlah', 'satuan','tanggal_perolehan', 'harga_perolehan')
                         ->groupBy('kode_aset', 'nama_barang', 'kategori', 'keterangan', 'lokasi', 
                                   'penanggung_jawab', 'jumlah', 'satuan', 'tanggal_perolehan', 'harga_perolehan');
                         
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