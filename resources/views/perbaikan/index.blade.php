@extends('layouts.app')

@section('page-title', 'Daftar Perbaikan Aset')

@section('content')

{{-- FILTER CARD --}}
<div class="card card-custom mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form action="{{ route('perbaikan.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                
                {{-- Filter Aset (Select2) --}}
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

                {{-- Filter Penanggung Jawab (Select2) --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Penanggung Jawab</label>
                    <select name="penanggung_jawab" id="filterPJ" class="form-select select2-init" data-placeholder="Semua PJ">
                        <option value="">Semua PJ</option>
                        @foreach($dataPJ as $pj)
                            <option value="{{ $pj }}" {{ request('penanggung_jawab') == $pj ? 'selected' : '' }}>
                                {{ $pj }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status (Dropdown) --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Status</label>
                    <select name="status" class="form-select border-purple-light">
                        <option value="">Semua Status</option>
                        <option value="Proses" {{ request('status') == 'Proses' ? 'selected' : '' }}>Sedang Proses</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                {{-- Filter Tanggal Masuk --}}
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Tgl Masuk</label>
                    <input type="date" name="tgl_masuk" class="form-control" value="{{ request('tgl_masuk') }}">
                </div>

                {{-- FUNGSI BUTTON --}}
                <div class="col-12 d-flex justify-content-center gap-3">
                    {{-- Tombol Filter --}}
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary fw-bold" style="background-color: #883C8C; border-color: #883C8C;">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                    {{-- TOMBOL RESET --}}
                    @if(request()->hasAny(['aset_id', 'penanggung_jawab', 'status', 'tgl_masuk']))
                        <div class="col-md-1 d-grid">
                            <a href="{{ route('perbaikan.index') }}" class="btn btn-light text-danger fw-bold border">
                                <i class="fas fa-undo me-1"></i> Reset 
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DATA TABLE -->
<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom-purple-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-purple-dark">
            <i class="fas fa-tools me-2 text-purple"></i> Monitoring Service
        </h5>
        {{-- Fungsi Pembatas Akses (hanya untuk admin) --}}
        @if(Auth::user()->role_user == 'admin')
            <a href="{{ route('perbaikan.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus me-1"></i> Input Kerusakan Baru
            </a>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-purple-light">
                    <tr>
                        <th class="ps-4 text-purple-dark">Tgl Masuk</th>
                        <th class="text-purple-dark">Barang</th>
                        <th class="text-center text-purple-dark">Jml</th>
                        <th class="text-purple-dark">Kerusakan</th>
                        <th class="text-center text-purple-dark">Status</th>
                        <th class="text-end pe-4 text-purple-dark">Biaya & Nota</th>
                        @if(Auth::user()->role_user == 'admin')
                            <th class="text-center text-purple-dark">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($perbaikan as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $item->tgl_masuk->format('d M Y') }}</div>
                            <small class="text-muted font-size-13 text-uppercase">PJ: {{ $item->penanggung_jawab }} </small>
                        </td>
                        <td>
                            <div class="fw-bold text-purple-dark">{{ $item->aset->nama_barang ?? 'Dihapus' }}</div>
                            <small class="text-muted">{{ $item->aset->kode_aset ?? '-' }}</small>
                        </td>
                        <td class="text-center fw-bold text-danger">
                            {{ $item->jumlah_perbaikan }}
                        </td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                {{ $item->keterangan_kerusakan }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($item->status == 'Proses')
                                <span class="badge bg-warning text-dark rounded-pill">Sedang Proses</span>
                            @else
                                <span class="badge bg-success rounded-pill">Selesai</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if($item->status == 'Selesai')
                                <div class="fw-bold text-dark">Rp {{ number_format($item->biaya, 0, ',', '.') }}</div>
                                @if($item->bukti_nota)
                                    <a href="{{ asset('storage/'.$item->bukti_nota) }}" target="_blank" class="small text-purple text-decoration-none">
                                        <i class="fas fa-paperclip me-1"></i> Lihat Nota
                                    </a>
                                @else
                                    <small class="text-muted fst-italic">Tanpa Nota</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->status == 'Proses')
                                {{-- Fungsi Pembatas Akses (hanya untuk admin) --}}
                                @if(Auth::user()->role_user == 'admin')
                                    {{-- Tombol Trigger Modal --}}
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalSelesai{{ $item->id }}">
                                        <i class="fas fa-check me-1"></i> Selesaikan
                                    </button>
                                {{-- JIKA BUKAN ADMIN: TAMPILKAN INFO SAJA --}}
                                @else
                                    <span class="badge bg-light text-secondary border">
                                        <i class="fas fa-clock me-1"></i> Menunggu
                                    </span>
                                @endif
                            @else
                                <button class="btn btn-sm btn-light text-muted" disabled><i class="fas fa-check-circle"></i></button>
                            @endif
                        </td>
                    </tr>

                    {{-- FORM MODAL UNTUK MENYELESAIKAN PERBAIKAN --}}
                    <div class="modal fade" id="modalSelesai{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-purple-light">
                                    <h5 class="modal-title fw-bold text-purple-dark">Selesaikan Perbaikan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('perbaikan.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        
                                        <div class="alert alert-info py-2 small mb-3 border-0 bg-opacity-10 bg-primary text-primary">
                                            Barang: <strong>{{ $item->aset->nama_barang }}</strong><br>
                                            Kerusakan: {{ $item->keterangan_kerusakan }} <br>
                                            Total Unit yang Diservis: <strong>{{ $item->jumlah_perbaikan }} Unit</strong>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Tanggal Selesai</label>
                                            <input type="date" name="tgl_selesai" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Biaya Perbaikan</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">Rp</span>
                                                <input type="number" name="biaya" class="form-control" placeholder="0" required>
                                            </div>
                                        </div>

                                        {{-- (LOGIKA UTAMA) --}}
                                        <div class="mb-3 p-3 bg-light border rounded">
                                            <label class="form-label fw-bold small text-purple-dark">Hasil Akhir Perbaikan <span class="text-danger">*</span></label>
                                            <select name="kondisi_akhir" id="kondisiAkhir{{ $item->id }}" class="form-select border-purple-light mb-2" 
                                                    onchange="toggleInputGagal({{ $item->id }})" required>
                                                <option value="">-- Pilih Status Akhir --</option>
                                                <option value="Baik">✅ Semua Berhasil Diperbaiki ({{ $item->jumlah_perbaikan }} Unit)</option>
                                                <option value="Rusak Berat">❌ Ada yang Gagal / Tidak Bisa Diperbaiki</option>
                                            </select>

                                            {{-- INPUT JUMLAH GAGAL (Hidden by default) --}}
                                            <div id="boxJumlahGagal{{ $item->id }}" class="d-none animate-fade-in mt-2">
                                                <label class="form-label fw-bold small text-danger">Jumlah Unit Gagal</label>
                                                <input type="number" name="jumlah_gagal" class="form-control border-danger text-danger fw-bold" 
                                                       min="1" max="{{ $item->jumlah_perbaikan }}" placeholder="Masukkan jumlah yang gagal">
                                                <div class="form-text text-danger small mt-1">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Sisa unit (selisih) otomatis dianggap <strong>Berhasil (Baik)</strong>.
                                                    <br>Unit gagal akan dicatat di <strong>Log Aset Rusak</strong>.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Upload Bukti Nota</label>
                                            <input type="file" name="bukti_nota" class="form-control" accept="image/*">
                                            <div class="form-text small">Format: JPG, PNG (Max 2MB)</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Keterangan Tambahan</label>
                                            <textarea name="keterangan_perbaikan" class="form-control" rows="2" placeholder="Catatan teknisi..."></textarea>
                                        </div>

                                    </div>
                                    <div class="modal-footer bg-light border-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary px-4">Simpan & Selesai</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Belum ada data perbaikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- PAGINATION --}}
        <div class="px-4 py-3 border-top-purple-light d-flex justify-content-end align-items-center">
            <div>
                {{ $perbaikan->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Script Select2
    $(document).ready(function() {
        $('.select2-init').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%'
        });
    });

    // SCRIPT TOGGLE INPUT GAGAL 
    function toggleInputGagal(id) {
        var select = document.getElementById('kondisiAkhir' + id);
        var box = document.getElementById('boxJumlahGagal' + id);
        var input = box.querySelector('input');

        if (select.value === 'Rusak Berat') {
            box.classList.remove('d-none'); // Tampilkan box
            input.required = true; // Wajib diisi
            input.focus();
        } else {
            box.classList.add('d-none'); // Sembunyikan
            input.required = false;
            input.value = ''; // Reset nilai
        }
    }
</script>
@endpush

<style>
    .bg-purple-light { background-color: #F5F0F6; }
    .text-purple { color: #883C8C; }
    .text-purple-dark { color: #2D0D34; }
    .border-bottom-purple-light { border-bottom: 1px solid #E6D7E9; }
    .border-purple-light { border-color: #E6D7E9 !important; }
    
    /* Animasi Halus */
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
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