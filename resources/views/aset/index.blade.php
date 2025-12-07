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
                    <p class="inv-hero-subtitle mb-0">Kelola seluruh data aset lahan, bangunan, dan barang yayasan.</p>
                </div>
            </div>
            <div class="text-end">
                {{-- Fungsi Pembatas Akses (hanya untuk admin) --}}
                @if(Auth::user()->role_user == 'admin')
                    <a href="{{ route('assets.create') }}" class="btn btn-light text-purple fw-bold rounded-pill shadow-sm mb-2">
                        <i class="fas fa-plus me-1"></i> Tambah Aset Baru
                    </a>
                @endif
                <div class="small inv-hero-muted text-end">Total Aset: {{ $totalJenisAset }} Item</div>
            </div>
        </div>
    </div>
</div>

{{-- FILTER CARD (BARU) --}}
<div class="card card-custom mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form action="{{ route('assets.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                
                {{-- SEARCH: No.Inv / NAMA ASET (SELECT2) --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Cari Aset</label>
                        <select name="search" id="filterSearch" class="form-select border-start-0">
                            <option value="">Semua Aset</option>
                            @foreach($dataAsetList as $aset)
                                {{-- Value kita set Kode Aset, Tampilan kita gabung Kode - Nama --}}
                                <option value="{{ $aset->kode_aset }}" 
                                    {{ request('search') == $aset->kode_aset ? 'selected' : '' }}>
                                    {{ $aset->kode_aset }} - {{ $aset->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                </div>

                {{-- FILTER: PENANGGUNG JAWAB (SELECT2) --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Penanggung Jawab</label>
                    <select name="penanggung_jawab" id="filterPJ" class="form-select select2-init" data-placeholder="Semua PJ">
                        <option value="">Semua PJ</option>
                        @foreach($dataPJ as $pj)
                            <option value="{{ $pj }}" {{ request('penanggung_jawab') == $pj ? 'selected' : '' }}>{{ $pj }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- FILTER: KONDISI (DROPDOWN BIASA) --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Kondisi</label>
                    <select name="kondisi" class="form-select border-purple-light">
                        <option value="">Semua Kondisi</option>
                        <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                        <option value="Rusak Ringan" {{ request('kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="Rusak Berat" {{ request('kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                </div>

                {{-- Filter Kategori (Select2) --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Filter Kategori</label>
                    <select name="kategori" id="filterKategori" class="form-select select2-init" data-placeholder="Semua Kategori">
                        <option value="">Semua Kategori</option>
                        @foreach($dataKategori as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Lokasi (Select2) --}}
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Filter Lokasi</label>
                    <select name="lokasi" id="filterLokasi" class="form-select select2-init" data-placeholder="Semua Lokasi">
                        <option value="">Semua Lokasi</option>
                        @foreach($dataLokasi as $loc)
                            <option value="{{ $loc }}" {{ request('lokasi') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter --}}
               <div class="col-12 d-flex justify-content-center gap-3">
                    {{-- Tombol Filter --}}
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary fw-bold" style="background-color: #883C8C; border-color: #883C8C;">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>

                    @if(request()->anyFilled(['search', 'kategori', 'lokasi','kondisi','penanggung_jawab']))
                        <div class="col-md-1 d-grid">
                            <a href="{{ route('assets.index') }}" class="btn btn-light text-danger fw-bold border">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TABEL DATA --}}
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-purple-light">
        <div class="d-flex align-items-center gap-3">
            <h6 class="m-0 fw-bold text-purple-dark">Tabel Data Aset</h6>
        </div>

        {{-- Tombol Export --}}
        <div class="d-flex gap-2">
            <a href="{{ route('assets.export.csv') . (request()->has('search') ? '?search=' . request('search') : '') }}" class="btn btn-sm btn-success text-white">
                <i class="fas fa-file-excel me-1"></i> CSV
            </a>
            <a href="{{ route('assets.export.pdf') . (request()->has('search') ? '?search=' . request('search') : '') }}" class="btn btn-sm btn-danger text-white">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-purple-light">
                    <tr>
                        <th width="5%" class="text-center text-purple-dark ps-4">No</th>
                        <th class="text-purple-dark">No.Inv & Nama Aset</th>
                        <th class="text-purple-dark">Kategori</th>
                        <th class="text-purple-dark">Lokasi & PJ</th>
                        <th class="text-center text-purple-dark">Stok</th>
                        <th class="text-center text-purple-dark">Kondisi</th>
                        @if(Auth::user()->role_user == 'admin')
                            <th width="150px" class="text-center text-purple-dark pe-4">Aksi</th>
                        @endif
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
                                        <small class="text-muted font-size-11 text-uppercase">No: <span class="text-purple fw-bold">{{ $item->kode_aset }}</span></small>
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
                        
                        {{-- KOLOM STOK (Data dari Controller) --}}
                        <td class="text-center">
                            @if($item->total_stok > 0)
                                <div class="fw-bold fs-5 text-purple-dark">{{ $item->total_stok }}</div>
                                <span class="fw-normal text-muted small">{{ $item->satuan }}</span>
                            @else
                                <span class="badge bg-danger">Habis</span>
                            @endif
                        </td>

                        {{-- KOLOM KONDISI (Data dari Controller) --}}
                        <td class="text-center">
                            <div class="d-flex flex-column gap-1 align-items-center">
                                @if($item->stok_baik > 0) 
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2">{{ $item->stok_baik }} Baik</span> 
                                @endif
                                @if($item->stok_rusak_ringan > 0) 
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-2">{{ $item->stok_rusak_ringan }} RR</span> 
                                @endif
                                @if($item->stok_rusak_berat > 0) 
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2">{{ $item->stok_rusak_berat }} RB</span> 
                                @endif
                                @if($item->sedang_diperbaiki > 0) 
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 mt-1">{{ $item->sedang_diperbaiki }} Service</span> 
                                @endif
                            </div>
                        </td>
                        {{-- Fungsi Pembatas Akses (hanya untuk admin) --}}
                        @if(Auth::user()->role_user == 'admin')
                            <td class="text-center pe-4">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#modalQr{{ $item->id }}"><i class="fas fa-qrcode"></i></button>
                                    <a href="{{ route('assets.edit', $item->id) }}" class="btn btn-sm btn-warning text-white"><i class="fas fa-edit"></i></a>
                                    <form onsubmit="return confirm('Hapus aset ini?');" action="{{ route('assets.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="hapus_by_kode" value="1">
                                        <input type="hidden" name="kode_aset" value="{{ $item->kode_aset }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 0 5px 5px 0;"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                    
                    {{-- MODAL QR (INCLUDE LANGSUNG AGAR COMPACT) --}}
                    <div class="modal fade" id="modalQr{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content text-center">
                                <div class="modal-header border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body pb-4" id="printableArea{{ $item->id }}">
                                    <h6 class="fw-bold text-purple-dark mb-3">Barcode Aset</h6>
                                    <div class="d-flex justify-content-center mb-3 p-2 bg-white rounded border">
                                        <img src="{{ (new \chillerlan\QRCode\QRCode)->render(route('assets.show', $item->id)) }}" alt="QR" style="width:100%; max-width:150px;">
                                    </div>
                                    <div class="fw-bold fs-5 text-dark">{{ $item->kode_aset }}</div>
                                    <div class="small text-muted">{{ $item->nama_barang }}</div>
                                </div>
                                <div class="modal-footer justify-content-center border-0 pt-0">
                                    <button class="btn btn-sm btn-primary w-100" onclick="printLabel('{{ $item->id }}')"><i class="fas fa-print me-1"></i> Cetak</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- PAGINATION --}}
        <div class="px-4 py-3 border-top-purple-light d-flex justify-content-end align-items-center">
            <div>
                {{ $assets->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
   // INISIALISASI SELECT2
    $(document).ready(function() {
        console.log('Select2 Initializing...'); // Cek di console log
        $('#filterSearch').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            placeholder: 'Ketik No.Inv / Nama...',
            width: '100%',
            language: { noResults: function() { return "Ketik untuk mencari..."; } }
        });
        $('#filterKategori').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: 'Semua Kategori'
        });
        
        $('#filterLokasi').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: 'Semua Lokasi'
        });
        $('#filterPJ').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: 'Semua Penanggung Jawab'
        });
    });

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
@endpush
<style>
    /* STYLE AGAR SAAT PRINT HANYA MODAL YANG MUNCUL */
    @media print {
        body * {
            visibility: hidden;
        }

        .modal-content * {
            visibility: visible;
        }

        .modal.show {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            visibility: visible;
        }

        .modal-dialog {
            margin: 0;
            width: 100%;
            max-width: 100%;
        }

        .modal-footer,
        .btn-close {
            display: none;
        }

        /* Sembunyikan tombol saat print */
    }

    /* STYLE SAMA SEPERTI SEBELUMNYA */
    .text-purple {
        color: #883C8C !important;
    }

    .text-purple-dark {
        color: #2D0D34 !important;
    }

    .bg-purple-light {
        background-color: #F5F0F6 !important;
    }

    .bg-purple-subtle {
        background-color: #E6D7E9 !important;
        color: #5A1968 !important;
    }

    .border-purple-light {
        border-color: #E6D7E9 !important;
    }

    .border-bottom-purple-light {
        border-bottom: 1px solid #E6D7E9 !important;
    }

    .border-top-purple-light {
        border-top: 1px solid #E6D7E9 !important;
    }

    .btn-outline-purple {
        color: #883C8C;
        border-color: #883C8C;
    }

    .btn-outline-purple:hover {
        background-color: #883C8C;
        color: white;
    }

    .btn-light.text-purple {
        background: white;
        color: #883C8C !important;
    }

    .btn-light.text-purple:hover {
        background: #f0e6f2;
        color: #5A1968 !important;
    }

    .table thead th {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom-width: 1px;
        padding-top: 16px;
        padding-bottom: 16px;
    }

    .table tbody td {
        padding-top: 16px;
        padding-bottom: 16px;
        border-bottom-color: #F5F0F6;
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
        border-radius: 10px;
    }

    .inv-hero-card {
        background: radial-gradient(circle at top left, #a45eb9 0, #883C8C 40%, #5A1968 90%);
        border-radius: 24px;
        padding: 24px;
        color: #F8F2F8;
        box-shadow: 0 18px 40px rgba(90, 25, 104, 0.25);
        position: relative;
        overflow: hidden;
    }

    .inv-hero-card::after {
        content: '';
        position: absolute;
        right: -32px;
        top: -32px;
        width: 120px;
        height: 120px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(230, 215, 233, 0.4), transparent);
    }

    .inv-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        background: rgba(45, 13, 52, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        box-shadow: 0 10px 26px rgba(45, 13, 52, 0.3);
        color: #F8F2F8;
    }

    .inv-hero-kicker {
        font-size: 11px;
        letter-spacing: .12em;
        text-transform: uppercase;
        font-weight: 600;
        opacity: .8;
    }

    .inv-hero-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .inv-hero-subtitle {
        font-size: 14px;
        opacity: .9;
    }

    .inv-hero-muted {
        color: rgba(248, 242, 248, 0.8);
        font-weight: 600;
    }

    .pagination .page-item.active .page-link {
        background-color: #883C8C;
        border-color: #883C8C;
    }

    .pagination .page-link {
        color: #883C8C;
    }
    /* Custom Style untuk Select2  */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #E6D7E9;
        font-size: 14px;
        padding: 0.375rem 0.75rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single {
        height: calc(2.2rem + 2px); /* Sesuaikan tinggi dengan input bootstrap */
    }

    /* CUSTOM PAGINATION STYLE (MUNZALAN THEME) */
    
    /* Container Pagination */
    .pagination {
        margin-bottom: 0;
        gap: 5px; /* Memberi jarak antar kotak angka */
    }

    /* Tombol Default */
    .page-item .page-link {
        color: #883C8C; /* Teks Ungu */
        border: 1px solid #E6D7E9; /* Border Ungu Muda */
        border-radius: 6px; /* Sudut tumpul yang manis */
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 600;
        background-color: white;
        transition: all 0.2s ease-in-out;
    }

    /* Saat Hover (Mouse Lewat) */
    .page-item .page-link:hover {
        background-color: #f8f0fa; /* Latar ungu sangat muda */
        color: #5A1968; /* Teks ungu tua */
        border-color: #883C8C;
        z-index: 2;
    }

    /* Tombol Aktif (Halaman Sekarang) */
    .page-item.active .page-link {
        background-color: #883C8C; /* Latar Ungu Utama */
        border-color: #883C8C;
        color: white;
        box-shadow: 0 3px 8px rgba(136, 60, 140, 0.3); /* Efek bayangan halus */
    }

    /* Tombol Disabled (Prev/Next mentok) */
    .page-item.disabled .page-link {
        color: #b0b0b0;
        background-color: #f9f9f9;
        border-color: #eee;
    }

    /* Hilangkan outline biru bawaan browser saat diklik */
    .page-link:focus {
        box-shadow: 0 0 0 3px rgba(136, 60, 140, 0.15);
    }
</style>
@endsection