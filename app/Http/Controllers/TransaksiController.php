<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\AsetModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
   public function index(Request $request)
    {
        // 1. Query Dasar (Tanpa filter jenis_transaksi)
        $query = Transaksi::with('aset');

        // 2. Filter Berdasarkan Aset (Pilihan dari Select2)
        // Jika user memilih aset di dropdown, kita filter berdasarkan aset_id
        if ($request->filled('aset_id')) {
            $query->where('aset_id', $request->aset_id);
        }

        // FILTER PENANGGUNG JAWAB 
        if ($request->filled('penerima')) {
            $query->where('penerima', $request->penerima);
        }

        // 3. Filter Tanggal 
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_keluar', [$request->start_date, $request->end_date]);
        }

        // 4. Ambil Data Transaksi (Paginate)
        $transaksi = $query->latest()->paginate(10)->withQueryString();

        // 3. DATA UNTUK DROPDOWN (LOGIKA BARU)
        // Ambil ID aset apa saja yang ada di tabel transaksi (Log Rusak)
        $idAsetRusak = Transaksi::distinct()->pluck('aset_id');
        $dataPJ     = Transaksi::select('penerima')->distinct()->pluck('penerima');

        // 5. Ambil Data Aset untuk Isi Dropdown Select2
        // Kita ambil ID, Kode, dan Nama untuk ditampilkan di pencarian
        $dataAset = AsetModel::withTrashed()
                                ->whereIn('id', $idAsetRusak)
                                ->select('id', 'kode_aset', 'nama_barang')
                                ->orderBy('nama_barang')
                                ->get();

        return view('transaksi.index', compact('transaksi', 'dataAset', 'dataPJ'));
    }
    
    // Tampilkan Form Barang Keluar
    public function create()
    {
        // Kita butuh daftar aset untuk dipilih di dropdown
        $assets = AsetModel::where('jumlah', '>', 0)->get(); // Hanya aset yg ada stoknya
        return view('transaksi.keluar', compact('assets'));
    }

    // Simpan Data Rusak
    public function store(Request $request)
    {
        $request->validate([
            'aset_id'        => 'required|exists:tb_aset,id',
            'tindakan'       => 'required|in:lapor_rusak,musnahkan', // Validasi baru
            'tanggal_keluar' => 'required|date',
            'jumlah_keluar'  => 'required|integer|min:1',
            'penerima'       => 'required|string',
            'alasan'         => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $asetAsal = AsetModel::findOrFail($request->aset_id);

            // Cek stok cukup
            if($asetAsal->jumlah < $request->jumlah_keluar) {
                throw new \Exception("Stok tidak cukup.");
            }

            // --- SKENARIO 1: HANYA LAPOR RUSAK (MUTASI) ---
            if ($request->tindakan == 'lapor_rusak') {
                
                // 1. Kurangi stok di data asal (misal dari data Baik)
                $asetAsal->decrement('jumlah', $request->jumlah_keluar);

                // 2. Cari/Buat data penampung "Rusak Berat"
                $asetRusak = AsetModel::where('kode_aset', $asetAsal->kode_aset)
                                      ->where('kondisi', 'Rusak Ringan')
                                      ->first();

                if ($asetRusak) {
                    $asetRusak->increment('jumlah', $request->jumlah_keluar);
                    $targetId = $asetRusak->id;
                } else {
                    $newRusak = $asetAsal->replicate();
                    $newRusak->kondisi = 'Rusak Ringan';
                    $newRusak->jumlah = $request->jumlah_keluar;
                    $newRusak->push();
                    $targetId = $newRusak->id;
                }

                // 3. Catat Log (Jenis: Mutasi/Laporan)
                Transaksi::create([
                    'aset_id'           => $targetId, // Link ke barang rusak
                    'jenis_transaksi'   => 'Laporan Rusak',
                    'tanggal_keluar'    => $request->tanggal_keluar,
                    'jumlah_keluar'     => $request->jumlah_keluar,
                    'penerima'          => $request->penerima,
                    'alasan'            => '[Status Berubah] ' . $request->alasan,
                    'biaya_tanggungan'  => 0,
                ]);

            // --- SKENARIO 2: MUSNAHKAN (DISPOSAL) ---
            } else {
                
                // 1. Kurangi stok langsung (Hilang dari sistem)
                $asetAsal->decrement('jumlah', $request->jumlah_keluar);

                // 2. Catat Log (Jenis: Keluar/Musnah)
                Transaksi::create([
                    'aset_id'           => $asetAsal->id,
                    'jenis_transaksi'   => 'Keluar', // Penanda barang hilang
                    'tanggal_keluar'    => $request->tanggal_keluar,
                    'jumlah_keluar'     => $request->jumlah_keluar,
                    'penerima'          => $request->penerima,
                    'alasan'            => '[DIMUSNAHKAN] ' . $request->alasan,
                    'biaya_tanggungan'  => 0,
                ]);
            }
        });

        $msg = $request->tindakan == 'lapor_rusak' 
            ? 'Barang berhasil dilaporkan rusak (Stok tetap ada di sistem sebagai Rusak Berat).' 
            : 'Barang berhasil dimusnahkan (Stok dikurangi permanen).';

        return redirect()->route('transaksi.index')->with('success', $msg);
    }
}