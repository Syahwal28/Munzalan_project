@extends('layouts.app')

@section('page-title', 'Input Aset Rusak')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Form Penghapusan Aset
                </h5>
                <p class="mb-0 small text-muted">Gunakan form ini untuk mencatat barang yang sudah rusak total dan tidak bisa diperbaiki lagi.</p>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('transaksi.store') }}" method="POST">
                    @csrf
                    
                    {{-- Hidden: Jenis Transaksi ini otomatis 'Keluar' --}}
                    <input type="hidden" name="jenis_transaksi" value="Keluar">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Aset yang Rusak</label>
                        <select name="aset_id" class="form-select select2" required>
                            <option value="">-- Cari Barang --</option>
                            @foreach($assets as $aset)
                                <option value="{{ $aset->id }}">
                                    {{ $aset->kode_aset }} - {{ $aset->nama_barang }} (Stok: {{ $aset->jumlah }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-danger small">* Stok akan otomatis berkurang permanen.</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tanggal Pencatatan</label>
                            <input type="date" name="tanggal_keluar" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Jumlah Rusak</label>
                            <input type="number" name="jumlah_keluar" class="form-control" min="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Dilaporkan Oleh (PJ)</label>
                        <input type="text" name="penerima" class="form-control" placeholder="Nama Pelapor" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Penyebab Kerusakan / Keterangan</label>
                        <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Terbakar, Hancur karena banjir, Hilang dicuri, dll." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('transaksi.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-danger">Simpan Data & Kurangi Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection