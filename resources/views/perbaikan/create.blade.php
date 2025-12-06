@extends('layouts.app')

@section('page-title', 'Input Perbaikan')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('perbaikan.index') }}" class="text-decoration-none text-muted">Monitoring Service</a>
                </li>
                <li class="breadcrumb-item active text-purple fw-bold">Input Kerusakan</li>
            </ol>
        </nav>

        {{-- Hero Card (TEMA UNGU) --}}
        <div class="inv-hero-card d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="inv-hero-icon">
                    <i class="fas fa-wrench"></i>
                </div>
                <div>
                    <div class="inv-hero-kicker mb-1">
                        Maintenance System
                    </div>
                    <h2 class="inv-hero-title mb-1">
                        Lapor Kerusakan Aset
                    </h2>
                    <p class="inv-hero-subtitle mb-0">
                        Catat barang yang akan diperbaiki agar stok terpotong sementara.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="inv-form-card">
            <div class="inv-form-header d-flex align-items-center gap-3">
                <div class="inv-form-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-purple-dark">
                        Form Pengajuan Service
                    </h5>
                    <p class="mb-0 small text-muted">
                        Isi detail kerusakan dan penanggung jawab.
                    </p>
                </div>
            </div>

            <div class="inv-form-body">
                <form action="{{ route('perbaikan.store') }}" method="POST">
                    @csrf

                    {{-- PILIH ASET --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-purple-dark">Pilih Aset <span class="text-danger">*</span></label>
                        <select name="aset_id" id="asetSelect" class="form-select select2 @error('aset_id') is-invalid @enderror" onchange="updateMaxStok()">
                            <option value="">-- Cari Aset --</option>
                            @foreach($assets as $aset)
                                <option value="{{ $aset->id }}">
                                    {{ $aset->kode_aset }} - {{ $aset->nama_barang }} ({{ $aset->kondisi }} - Stok: {{ $aset->jumlah }})
                                </option>
                            @endforeach
                        </select>
                        @error('aset_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="inv-field-hint">Hanya barang yang memiliki stok > 0 dan berstatus Rusak yang muncul disini.</div>
                    </div>

                    {{-- INPUT JUMLAH --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-purple-dark">Jumlah Perbaikan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="jumlah_perbaikan" id="jumlahInput" class="form-control @error('jumlah_perbaikan') is-invalid @enderror" 
                                   value="{{ old('jumlah_perbaikan', 1) }}" min="1" required>
                            <span class="input-group-text bg-white text-muted">Unit</span>
                            @error('jumlah_perbaikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-text text-muted" id="stokHint">Maksimal sesuai stok yang tersedia.</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-purple-dark">Tanggal Masuk Service</label>
                            <input type="date" name="tgl_masuk" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-purple-dark">Penanggung Jawab</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-purple"><i class="fas fa-user"></i></span>
                                <input type="text" name="penanggung_jawab" class="form-control" 
                                       placeholder="Nama Pengantar / PJ" value="{{ old('penanggung_jawab') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-purple-dark">Deskripsi Kerusakan <span class="text-danger">*</span></label>
                        <textarea name="keterangan_kerusakan" class="form-control @error('keterangan_kerusakan') is-invalid @enderror" 
                                  rows="3" placeholder="Contoh: Layar mati total, Kaki kursi patah, dll..." required>{{ old('keterangan_kerusakan') }}</textarea>
                        @error('keterangan_kerusakan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="inv-form-divider">

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary px-4 inv-btn-save shadow-sm">
                            <i class="fas fa-save me-2"></i>Simpan Data
                        </button>
                        <a href="{{ route('perbaikan.index') }}" class="btn btn-light text-purple fw-bold px-4 rounded-pill border">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Info Card --}}
    <div class="col-lg-4">
        <div class="alert alert-light border-purple-light shadow-sm p-4 rounded-4">
            <h6 class="fw-bold text-purple-dark mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Sistem</h6>
            <ul class="small text-muted ps-3 mb-0" style="line-height: 1.8;">
                <li>Saat Anda menyimpan data ini, status perbaikan akan menjadi <strong>"Proses"</strong>.</li>
                <li>Stok barang yang dipilih akan otomatis <strong>dikurangi</strong> sesuai jumlah yang Anda input.</li>
                <li>Setelah barang selesai diperbaiki, klik tombol <strong>"Selesaikan"</strong> di tabel monitoring untuk mengembalikan stok.</li>
            </ul>
        </div>
    </div>
</div>

<style>
    /* Menggunakan style tema ungu yang sama */
    .text-purple { color: #883C8C !important; }
    .text-purple-dark { color: #5A1968 !important; }
    .border-purple-light { border-color: #E6D7E9 !important; }

    /* HERO CARD */
    .inv-hero-card {
        background: radial-gradient(circle at top left, #a45eb9 0, #883C8C 40%, #5A1968 90%);
        border-radius: 24px; padding: 18px 20px; color: #F8F2F8;
        box-shadow: 0 18px 40px rgba(90, 25, 104, 0.35);
        position: relative; overflow: hidden;
    }
    .inv-hero-card::after {
        content: ''; position: absolute; right: -32px; top: -32px; width: 120px; height: 120px;
        border-radius: 999px; background: radial-gradient(circle, rgba(230, 215, 233, 0.5), transparent);
    }
    .inv-hero-icon {
        width: 48px; height: 48px; border-radius: 18px;
        background: rgba(45, 13, 52, 0.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; box-shadow: 0 10px 26px rgba(45, 13, 52, 0.4); color: #F8F2F8;
    }
    .inv-hero-kicker { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; font-weight: 600; opacity: .9; }
    .inv-hero-title { font-size: 21px; font-weight: 700; letter-spacing: -0.03em; }
    .inv-hero-subtitle { font-size: 13px; opacity: .96; }

    /* FORM CARD */
    .inv-form-card {
        background: #ffffff; border-radius: 20px; border: 1px solid #E6D7E9;
        box-shadow: 0 18px 40px rgba(90, 25, 104, 0.06); overflow: hidden;
    }
    .inv-form-header { padding: 16px 20px; border-bottom: 1px solid #E6D7E9; background: linear-gradient(135deg, #fdf4ff, #f3e8ff); }
    .inv-form-icon { width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #d8b4fe, #883C8C); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .inv-form-body { padding: 24px; }
    .inv-form-divider { border-color: #E6D7E9; margin: 22px 0; }
    .inv-field-hint { font-size: 11px; color: #883C8C; margin-top: 4px; opacity: 0.8; }
    .inv-btn-save { border-radius: 999px; font-weight: 600; }

    /* INPUT STYLING */
    .inv-form-body .form-control, .inv-form-body .form-select { border-radius: 10px; border-color: #E6D7E9; font-size: 14px; padding: 10px 14px; transition: all 0.2s; }
    .inv-form-body .form-control:focus, .inv-form-body .form-select:focus { border-color: #883C8C; box-shadow: 0 0 0 3px rgba(136, 60, 140, 0.15); }
    
    .inv-form-body .input-group .form-control { border-radius: 10px 0 0 10px; }
    .inv-form-body .input-group .input-group-text { border-radius: 0 10px 10px 0; background-color: #fdf4ff; border-color: #E6D7E9; color: #5A1968; }
    .inv-form-body .input-group .input-group-text:first-child { border-radius: 10px 0 0 10px; border-right: 0; }
    .inv-form-body .input-group .form-control:last-child { border-radius: 0 10px 10px 0; border-left: 0; }
    .inv-form-body .input-group .form-control:focus + .input-group-text { border-color: #883C8C; z-index: 3; }
</style>

{{-- SCRIPT UNTUK BATASI INPUT AGAR TIDAK LEBIH DARI STOK --}}
<script>
    function updateMaxStok() {
        var select = document.getElementById('asetSelect');
        var input = document.getElementById('jumlahInput');
        var hint = document.getElementById('stokHint');
        
        // Ambil stok dari data-stok option yang dipilih
        var selectedOption = select.options[select.selectedIndex];
        var maxStok = selectedOption.getAttribute('data-stok');

        if(maxStok) {
            input.max = maxStok; // Set batas maksimal input html5
            hint.innerHTML = "Stok saat ini: <strong>" + maxStok + "</strong> unit. Anda tidak bisa input lebih dari ini.";
            hint.className = "form-text text-success";
        } else {
            input.removeAttribute('max');
            hint.innerHTML = "Maksimal sesuai stok yang tersedia.";
            hint.className = "form-text text-muted";
        }
    }
</script>

@endsection