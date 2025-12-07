@extends('layouts.app')

@section('page-title', 'Input Aset Rusak')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Pencatatan Aset Rusak
                </h5>
                <p class="mb-0 small text-muted">Kelola barang rusak: Laporkan kerusakan atau hapus barang yang dimusnahkan.</p>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('transaksi.store') }}" method="POST">
                    @csrf
                    
                    {{-- PILIH BARANG --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Aset</label>
                        <select name="aset_id" class="form-select select2-init" required>
                            <option value="">-- Cari Aset --</option>
                            @foreach($assets as $aset)
                                <option value="{{ $aset->id }}">
                                    {{ $aset->kode_aset }} - {{ $aset->nama_barang }} ({{ $aset->kondisi }} - Stok: {{ $aset->jumlah }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NEW: PILIH TINDAKAN --}}
                    <div class="mb-4 p-3 bg-light border rounded">
                        <label class="form-label fw-bold text-purple-dark">Jenis Tindakan <span class="text-danger">*</span></label>
                        <select name="tindakan" class="form-select" required>
                            <option value="lapor_rusak">📝 Lapor Rusak (Stok Tetap Ada, Status Berubah)</option>
                            <option value="musnahkan">🗑️ Musnahkan / Buang (Stok Berkurang Permanen)</option>
                        </select>
                        <div class="form-text mt-2 small text-muted">
                            <strong>Lapor Rusak:</strong> Barang masih di gudang tapi kondisinya rusak.<br>
                            <strong>Musnahkan:</strong> Barang dibuang/dijual rongsok (hilang dari gudang).
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tanggal</label>
                            <input type="date" name="tanggal_keluar" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Jumlah</label>
                            <input type="number" name="jumlah_keluar" class="form-control" min="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Penanggung Jawab / Saksi</label>
                        <input type="text" name="penerima" class="form-control" placeholder="Nama PJ" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Keterangan / Alasan</label>
                        <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Rusak dimakan rayap, atau Dibakar karena hancur total..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('transaksi.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-danger">Simpan</button>
                    </div>
                </form>
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
@endsection