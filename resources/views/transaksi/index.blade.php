@extends('layouts.app')

@section('page-title', 'Log Penghapusan Aset')

@section('content')

{{-- FILTER CARD --}}
<div class="card card-custom mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form action="{{ route('transaksi.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                
                {{-- FILTER ASET (SELECT2) --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Cari Aset</label>
                    <select name="aset_id" id="filterAset" class="form-select select2-init" data-placeholder="Ketik No.Inv / Nama...">
                        <option value="">Semua Aset</option>
                        @foreach($dataAset as $aset)
                            <option value="{{ $aset->id }}" {{ request('aset_id') == $aset->id ? 'selected' : '' }}>
                                {{ $aset->kode_aset }} - {{ $aset->nama_barang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- FILTER PENANGGUNG JAWAB --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Penanggung Jawab</label>
                    <select name="penerima" id="filterPJ" class="form-select select2-init" data-placeholder="Semua PJ">
                        <option value="">Semua PJ</option>
                        @foreach($dataPJ as $pj)
                            <option value="{{ $pj }}" {{ request('penerima') == $pj ? 'selected' : '' }}>{{ $pj }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- FILTER TANGGAL --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                
                {{-- Filter Tanggal Selesai --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                {{-- FUNGSI BUTTON --}}
                <div class="col-12 d-flex justify-content-center gap-3">
                    {{-- TOMBOL FILTER --}}
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary fw-bold" style="background-color: #883C8C; border-color: #883C8C;">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>

                    {{-- TOMBOL RESET --}}
                    @if(request()->hasAny(['aset_id','penerima','start_date','end_date']))
                        <div class="col-md-1 d-grid">
                            <a href="{{ route('transaksi.index') }}" class="btn btn-light text-danger fw-bold border">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
{{-- DATA TABLE --}}
<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom-purple-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-purple-dark">
            <i class="fas fa-archive me-2 text-purple"></i> Daftar Aset Rusak Berat / Musnah
        </h5>
        {{-- Fungsi Pembatas Akses (hanya untuk admin) --}}
        @if(Auth::user()->role_user == 'admin')
            <a href="{{ route('transaksi.create') }}" class="btn btn-sm btn-danger shadow-sm">
                <i class="fas fa-minus-circle me-1"></i> Catat Aset Rusak
            </a>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-purple-light">
                    <tr>
                        <th class="ps-4 text-purple-dark">Tanggal Catat</th>
                        <th class="text-purple-dark">Barang / Aset</th>
                        <th class="text-purple-dark">Keterangan Kerusakan</th>
                        <th class="text-center text-purple-dark">Jumlah</th>
                        <th class="text-end pe-4 text-purple-dark">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $item->tanggal_keluar->format('d M Y') }}</div>
                            <small class="text-muted">Oleh: {{ $item->penerima }}</small> 
                            {{-- Di sini 'penerima' kita anggap sebagai 'Pelapor/Penanggung Jawab' --}}
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-danger bg-opacity-10 text-danger rounded me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-purple-dark">{{ $item->aset->nama_barang ?? 'Data Terhapus' }}</h6>
                                    {{-- Cek apakah aset sudah dihapus (soft deleted) --}}
                                    @if($item->aset && $item->aset->trashed())
                                        <span class="badge bg-danger" style="font-size: 8px;">(Aset Terhapus)</span>
                                    @endif
                                    <small class="text-muted">{{ $item->aset->kode_aset ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{-- Kita gunakan kolom 'alasan' untuk menyimpan detail kerusakan --}}
                            <span class="text-muted fst-italic">{{ $item->alasan }}</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger fs-5">{{ $item->jumlah_keluar }}</span>
                            <span class="small text-muted">{{ $item->aset->satuan ?? '' }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <span class="badge bg-secondary rounded-pill">Non-Aktif / Musnah</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="opacity-50">
                                <i class="fas fa-trash-alt fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Belum ada data aset yang dihapus/rusak berat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- PAGINATION --}}
        <div class="px-4 py-3 border-top-purple-light d-flex justify-content-end align-items-center">
            <div>
                {{ $transaksi->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
<!-- Script untuk select2 -->
@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-init').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush

<!-- Styling -->
<style>
    .bg-purple-light { 
        background-color: #F5F0F6; 
    }
    .text-purple { 
        color: #883C8C; 
    }
    .text-purple-dark { 
        color: #2D0D34; 
    }
    .border-bottom-purple-light { 
        border-bottom: 1px solid #E6D7E9; 
    }
    .avatar-sm { 
        width: 35px; 
        height: 35px; 
    }
    /* Select2 Style */
    .select2-container--bootstrap-5 .select2-selection { 
        border-color: #E6D7E9; 
        font-size: 14px; 
        padding: 0.375rem 0.75rem; 
    }
    .select2-container--bootstrap-5 .select2-selection--single { 
        height: calc(2.2rem + 2px); 
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