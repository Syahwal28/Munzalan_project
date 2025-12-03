@extends('layouts.app')

@section('page-title', 'Data Aset Munzalan')

@section('content')

{{-- HERO CARD --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="inv-hero-card d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="inv-hero-icon"><i class="fas fa-cubes"></i></div>
                <div>
                    <div class="inv-hero-kicker mb-1">Munzalan Inventory System</div>
                    <h2 class="inv-hero-title mb-1">Daftar Aset & Inventaris</h2>
                    <p class="inv-hero-subtitle mb-0">Kelola seluruh data aset lahan, bangunan, dan barang yayasan di sini.</p>
                </div>
            </div>
            <div class="text-end">
                @auth
                <a href="{{ route('assets.create') }}" class="btn btn-light text-purple fw-bold rounded-pill shadow-sm mb-2">
                    <i class="fas fa-plus me-1"></i> Tambah Aset Baru
                </a>
                @endauth
                <div class="small inv-hero-muted text-end">Total Aset: {{ $assets->total() }} Item</div>
            </div>
        </div>
    </div>
</div>

{{-- TABEL DATA --}}
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-purple-light">
        
        {{-- BLOK TOMBOL EXPORT --}}
        <div class="d-flex gap-2">
            <h6 class="m-0 fw-bold text-purple-dark me-2">Tabel Data Aset</h6>
            <a href="{{ route('assets.export.csv') . (request()->has('search') ? '?search=' . request('search') : '') }}" class="btn btn-sm btn-success text-white">
                <i class="fas fa-file-excel me-1"></i> Excel (CSV)
            </a>
            <a href="{{ route('assets.export.pdf') . (request()->has('search') ? '?search=' . request('search') : '') }}" class="btn btn-sm btn-danger text-white">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
        </div>
        
        {{-- BLOK PENCARIAN (DIGANTI MENGGUNAKAN FORM GET) --}}
        <form action="{{ route('assets.index') }}" method="GET" style="width: 250px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-purple-light text-purple"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control border-purple-light" placeholder="Cari data cepat..." value="{{ request('search') }}">
                @if(request('search'))
                    <a href="{{ route('assets.index') }}" class="input-group-text bg-light border-purple-light text-danger" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                @else
                    <button type="submit" class="d-none"></button> {{-- Tombol submit tersembunyi untuk input pencarian --}}
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-purple-light">
                    <tr>
                        <th width="5%" class="text-center text-purple-dark ps-4">No</th>
                        <th class="text-purple-dark">Kode & Nama Aset</th>
                        <th class="text-purple-dark">Kategori</th>
                        <th class="text-purple-dark">Lokasi & PJ</th>
                        <th class="text-center text-purple-dark">Stok Tersedia</th>
                        <th class="text-center text-purple-dark">Kondisi & Status</th>
                        @auth
                            <th width="150px" class="text-center text-purple-dark pe-4">Aksi</th>
                        @endauth
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $item)
                    <tr>
                        <td class="text-center ps-4 fw-bold text-purple">{{ $loop->iteration + $assets->firstItem() - 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-purple-subtle text-purple rounded me-3 d-flex align-items-center justify-content-center font-size-20">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-purple-dark">{{ $item->nama_barang }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted font-size-11 text-uppercase">Kode: <span class="text-purple fw-bold">{{ $item->kode_aset }}</span></small>
                                        {{-- Ikon Kecil Indikator QR --}}
                                        <i class="fas fa-qrcode text-muted" style="font-size: 10px;" title="QR Code Tersedia"></i>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-purple-subtle text-purple rounded-pill px-3">{{ $item->kategori }}</span>
                            <div class="small text-muted mt-1">{{ $item->sumber_aset }}</div>
                        </td>
                        <td>
                            <div class="text-purple-dark fw-semibold">{{ $item->penanggung_jawab }}</div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1 text-purple"></i>{{ $item->lokasi }}</small>
                        </td>
                        
                        {{-- LOGIKA STOK --}}
                        @php
                            $varianBarang = \App\Models\AsetModel::where('kode_aset', $item->kode_aset)->get();
                            $totalStok = $varianBarang->sum('jumlah');
                            $stokBaik = $varianBarang->where('kondisi', 'Baik')->sum('jumlah');
                            $stokRusakRingan = $varianBarang->where('kondisi', 'Rusak Ringan')->sum('jumlah');
                            $stokRusakBerat = $varianBarang->where('kondisi', 'Rusak Berat')->sum('jumlah');
                        @endphp
                        
                        <td class="text-center">
                            <div class="fw-bold fs-5 text-purple-dark">{{ $totalStok }}</div>
                            <span class="fw-normal text-muted small">{{ $item->satuan }}</span>
                        </td>

                        <td class="text-center">
                            <div class="d-flex flex-column gap-1 align-items-center">
                                @if($stokBaik > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">
                                        <i class="fas fa-check-circle me-1"></i> {{ $stokBaik }} Baik
                                    </span>
                                @endif
                                @if($stokRusakRingan > 0)
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 rounded-pill px-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $stokRusakRingan }} Rusak Ringan
                                    </span>
                                @endif
                                @if($stokRusakBerat > 0)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">
                                        <i class="fas fa-times-circle me-1"></i> {{ $stokRusakBerat }} Rusak Berat
                                    </span>
                                @endif
                            </div>
                        </td>

                        @auth
                        <td class="text-center pe-4">
                            <div class="btn-group" role="group">
                                {{-- TOMBOL QR CODE --}}
                                <button type="button" class="btn btn-sm btn-dark text-white" data-bs-toggle="modal" data-bs-target="#modalQr{{ $item->id ?? 0 }}" title="Scan Barcode">
                                    <i class="fas fa-qrcode"></i>
                                </button>

                                <a href="{{ route('assets.edit', $item->id ?? 0) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a
                                
                                <form onsubmit="return confirm('Hapus aset ini?');" action="{{ route('assets.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    {{-- Kirim Flag Hapus Group --}}
                                    <input type="hidden" name="hapus_by_kode" value="1">
                                    <input type="hidden" name="kode_aset" value="{{ $item->kode_aset }}">
                                    
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endauth
                    </tr>

                    {{-- MODAL QR CODE (CHILLERLAN) --}}
                    <div class="modal fade" id="modalQr{{ $item->id ?? 0 }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content text-center">
                                <div class="modal-header border-0 pb-0">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body pb-4" id="printableArea{{ $item->id }}">
                                    <h6 class="fw-bold text-purple-dark mb-3">Barcode Aset</h6>
                                    
                                    {{-- GENERATE QR CODE BERISI URL --}}
                                    <div class="d-flex justify-content-center mb-3 p-2 bg-white rounded">
                                        {{-- PENTING: Isi render() diganti menjadi URL Route Show --}}
                                        <img src="{{ (new \chillerlan\QRCode\QRCode)->render(route('assets.show', $item->id)) }}" 
                                            alt="QR Code" 
                                            style="width: 100%; max-width: 180px; height: auto; border: 1px solid #eee; padding: 5px;">
                                    </div>

                                    <div class="fw-bold fs-5 text-dark" style="font-family: monospace;">{{ $item->kode_aset }}</div>
                                    <div class="small text-muted text-uppercase">{{ $item->nama_barang }}</div>
                                </div>
                                <div class="modal-footer justify-content-center border-0 pt-0">
                                    {{-- Kita kirim HTML dari area print ke fungsi JS --}}
                                    <button class="btn btn-sm btn-primary w-100" onclick="printLabel('{{ $item->id }}')">
                                        <i class="fas fa-print me-1"></i> Cetak Label
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state">
                                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="Kosong" style="width: 80px; opacity: 0.5; filter: grayscale(100%) sepia(100%) hue-rotate(220deg) saturate(200%);">
                                <p class="text-muted mt-3 mb-0">Belum ada data aset yang tercatat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-top-purple-light d-flex justify-content-end">
            {{ $assets->links() }}
        </div>
    </div>
</div>

<script>
    function printLabel(id) {
        // Ambil isi HTML dari dalam modal (Gambar + Teks)
        var content = document.getElementById('printableArea' + id).innerHTML;
        
        // Buat Jendela Baru (Popup) khusus untuk print
        var printWindow = window.open('', '', 'height=600,width=600');
        
        printWindow.document.write('<html><head><title>Cetak Label Aset</title>');
        // Tambahkan style agar tampilan print rapi (Tengah & Font jelas)
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: sans-serif; text-align: center; padding: 20px; }');
        printWindow.document.write('img { width: 200px; height: auto; margin-bottom: 10px; }');
        printWindow.document.write('.kode { font-size: 24px; font-weight: bold; margin-bottom: 5px; }');
        printWindow.document.write('.nama { font-size: 14px; text-transform: uppercase; color: #555; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        
        // Masukkan konten QR
        printWindow.document.write(content);
        
        printWindow.document.write('</body></html>');
        
        printWindow.document.close();
        printWindow.focus();
        
        // Beri jeda sedikit agar gambar ter-load sempurna sebelum dialog print muncul
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>

<style>
    /* STYLE AGAR SAAT PRINT HANYA MODAL YANG MUNCUL */
    @media print {
        body * { visibility: hidden; }
        .modal-content * { visibility: visible; }
        .modal.show { position: absolute; left: 0; top: 0; width: 100%; height: 100%; margin: 0; padding: 0; visibility: visible; }
        .modal-dialog { margin: 0; width: 100%; max-width: 100%; }
        .modal-footer, .btn-close { display: none; } /* Sembunyikan tombol saat print */
    }
    /* STYLE SAMA SEPERTI SEBELUMNYA */
    .text-purple { color: #883C8C !important; }
    .text-purple-dark { color: #2D0D34 !important; }
    .bg-purple-light { background-color: #F5F0F6 !important; }
    .bg-purple-subtle { background-color: #E6D7E9 !important; color: #5A1968 !important; }
    .border-purple-light { border-color: #E6D7E9 !important; }
    .border-bottom-purple-light { border-bottom: 1px solid #E6D7E9 !important; }
    .border-top-purple-light { border-top: 1px solid #E6D7E9 !important; }

    .btn-outline-purple { color: #883C8C; border-color: #883C8C; }
    .btn-outline-purple:hover { background-color: #883C8C; color: white; }
    
    .btn-light.text-purple { background: white; color: #883C8C !important; }
    .btn-light.text-purple:hover { background: #f0e6f2; color: #5A1968 !important; }

    .table thead th { font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom-width: 1px; padding-top: 16px; padding-bottom: 16px; }
    .table tbody td { padding-top: 16px; padding-bottom: 16px; border-bottom-color: #F5F0F6; }
    .avatar-sm { width: 40px; height: 40px; border-radius: 10px; }

    .inv-hero-card { background: radial-gradient(circle at top left, #a45eb9 0, #883C8C 40%, #5A1968 90%); border-radius: 24px; padding: 24px; color: #F8F2F8; box-shadow: 0 18px 40px rgba(90, 25, 104, 0.25); position: relative; overflow: hidden; }
    .inv-hero-card::after { content: ''; position: absolute; right: -32px; top: -32px; width: 120px; height: 120px; border-radius: 999px; background: radial-gradient(circle, rgba(230, 215, 233, 0.4), transparent); }
    .inv-hero-icon { width: 56px; height: 56px; border-radius: 18px; background: rgba(45, 13, 52, 0.3); display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 10px 26px rgba(45, 13, 52, 0.3); color: #F8F2F8; }
    .inv-hero-kicker { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; font-weight: 600; opacity: .8; }
    .inv-hero-title { font-size: 24px; font-weight: 700; letter-spacing: -0.02em; }
    .inv-hero-subtitle { font-size: 14px; opacity: .9; }
    .inv-hero-muted { color: rgba(248, 242, 248, 0.8); font-weight: 600; }

    .pagination .page-item.active .page-link { background-color: #883C8C; border-color: #883C8C; }
    .pagination .page-link { color: #883C8C; }
</style>
@endsection
