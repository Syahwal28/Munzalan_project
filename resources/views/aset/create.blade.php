@extends('layouts.app')

@section('page-title', 'Input Aset Baru')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}" class="text-decoration-none text-muted">Data Aset</a></li>
                <li class="breadcrumb-item active text-purple fw-bold">Input Baru</li>
            </ol>
        </nav>

        {{-- Hero Card --}}
        <div class="inv-hero-card d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="inv-hero-icon"><i class="fas fa-plus-circle"></i></div>
                <div>
                    <div class="inv-hero-kicker mb-1">Sistem Aset Yayasan</div>
                    <h2 class="inv-hero-title mb-1">Registrasi Aset Baru</h2>
                    <p class="inv-hero-subtitle mb-0">Catat aset dengan penanggung jawab berbeda tiap baris.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="inv-form-card">
            <div class="inv-form-header d-flex align-items-center gap-3">
                <div class="inv-form-icon"><i class="fas fa-file-signature"></i></div>
                <div>
                    <h5 class="mb-1 fw-bold text-purple-dark">Formulir Data Aset</h5>
                    <p class="mb-0 small text-muted">Lengkapi data aset utama dan rincian fisiknya.</p>
                </div>
            </div>

            <div class="inv-form-body">
                <form action="{{ route('assets.store') }}" method="POST">
                    @csrf

                    {{-- === BAGIAN 1: INFORMASI UMUM === --}}
                    <h6 class="text-uppercase text-purple fw-bold small mb-3 letter-spacing-1">
                        <i class="fas fa-info-circle me-1"></i> Informasi Umum
                    </h6>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-purple-dark">Kode Aset Utama <span class="text-danger">*</span></label>
                            <input type="text" name="kode_aset_utama" class="form-control" value="{{ old('kode_aset_utama') }}" placeholder="Cth: INV-2024-001" required>
                            <div class="inv-field-hint">Kode otomatis diberi akhiran (-1, -2) jika input > 1 baris.</div>
                        </div>
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-purple-dark">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control" value="{{ old('nama_barang') }}" placeholder="Cth: Kursi Lipat Besi" required>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-purple-dark">Kategori</label>
                            
                            {{-- Dropdown Kategori --}}
                            <select name="kategori" id="kategoriSelect" class="form-select" onchange="toggleKategoriLainnya()" required>
                                <option value="Elektronik">Elektronik</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Kendaraan">Kendaraan</option>
                                <option value="Lahan & Bangunan">Lahan & Bangunan</option>
                                <option value="Peralatan">Peralatan</option>
                                <option value="Lainnya">Lainnya (Isi Manual)</option>
                            </select>

                            {{-- Input Manual Kategori (Hidden by Default) --}}
                            <input type="text" id="kategoriManual" class="form-control mt-2 d-none" placeholder="Tulis Kategori...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-purple-dark">Sumber</label>
                            <select name="sumber_aset" class="form-select" required>
                                <option value="Barang Pusda">Barang Pusda</option>
                                <option value="Dana Wakaf">Dana Wakaf</option>
                                <option value="Dana Operasional">Dana Operasional</option>
                                <option value="Barang Wakaf">Barang Wakaf
                            </select>
                        </div>
                    </div>

                    <hr class="inv-form-divider">

                    {{-- === BAGIAN 2: RINCIAN FISIK (TABLE) === --}}
                    <h6 class="text-purple-dark fw-bold mb-3">
                        <i class="fas fa-boxes me-1"></i> Rincian & Penanggung Jawab
                    </h6>

                    <div class="table-responsive mb-3 rounded-3 border border-light">
                        <table class="table align-middle mb-0" id="tableRincian">
                            <thead style="background-color: #fdf4ff;">
                                <tr>
                                    {{-- Atur Lebar Kolom Agar Rapi --}}
                                    <th width="12%" class="text-purple-dark ps-3">Jml <span class="text-danger">*</span></th>
                                    <th width="15%" class="text-purple-dark">Satuan</th>
                                    <th width="20%" class="text-purple-dark">Kondisi <span class="text-danger">*</span></th>
                                    <th width="25%" class="text-purple-dark">Penanggung Jawab <span class="text-danger">*</span></th>
                                    <th width="23%" class="text-purple-dark">Ket.</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="containerInput">
                                {{-- Baris Pertama (Wajib Ada) --}}
                                <tr class="input-row">
                                    <td class="ps-3">
                                        <input type="number" name="details[0][jumlah]" class="form-control" min="1" value="1" required placeholder="1">
                                    </td>
                                    <td>
                                        <select name="details[0][satuan]" class="form-select">
                                            <option value="Unit">Unit</option>
                                            <option value="Pcs">Pcs</option>
                                            <option value="Set">Set</option>
                                            <option value="Buah">Buah</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="details[0][kondisi]" class="form-select bg-white" required>
                                            <option value="Baik">✅ Baik</option>
                                            <option value="Rusak Ringan">⚠️ Rusak Ringan</option>
                                            <option value="Rusak Berat">❌ Rusak Berat</option>
                                        </select>
                                    </td>
                                    <td>
                                        {{-- INPUT PJ PINDAH KESINI --}}
                                        <input type="text" name="details[0][penanggung_jawab]" class="form-control" placeholder="Nama PJ" required>
                                    </td>
                                    <td>
                                        <input type="text" name="details[0][keterangan]" class="form-control" placeholder="Opsional">
                                    </td>
                                    <td class="text-center"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-dashed w-100 py-3 mb-4 d-flex align-items-center justify-content-center gap-2" onclick="addRow()">
                        <i class="fas fa-plus-circle"></i> Tambah Baris Lain
                    </button>

                    {{-- === BAGIAN 3: LOKASI (Tetap Global / Sekali isi) === --}}
                    <div class="row mb-3 p-3 bg-purple-light rounded border border-purple-light">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-purple-dark">Lokasi Penyimpanan (Gudang/Ruangan) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-purple"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="lokasi" class="form-control" placeholder="Cth: Gudang Utama, Ruang Guru" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-purple-dark">Tanggal Perolehan</label>
                            <input type="date" name="tanggal_perolehan" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-purple-dark">Harga Perolehan (Total)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-purple">Rp</span>
                                <input type="number" name="harga_perolehan" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4 inv-btn-save shadow-sm">
                            <i class="fas fa-save me-2"></i>Simpan Data
                        </button>
                        <a href="{{ route('assets.index') }}" class="btn btn-light text-purple fw-bold px-4 rounded-pill border">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JAVASCRIPT DYNAMIC ROW --}}
<script>
    function toggleKategoriLainnya() {
        var select = document.getElementById('kategoriSelect');
        var manualInput = document.getElementById('kategoriManual');

        if (select.value === 'Lainnya') {
            manualInput.classList.remove('d-none'); // Munculkan input text
            manualInput.required = true; // Wajib diisi
            select.removeAttribute('name'); // Hapus name dari select agar tidak terkirim
            manualInput.setAttribute('name', 'kategori'); // Input manual jadi field utama
            manualInput.focus();
        } else {
            manualInput.classList.add('d-none'); // Sembunyikan
            manualInput.required = false;
            manualInput.removeAttribute('name'); // Hapus name dari input manual
            select.setAttribute('name', 'kategori'); // Kembalikan name ke select
        }
    }
    
    let rowIndex = 1;

    function addRow() {
        const container = document.getElementById('containerInput');
        
        const newRow = `
            <tr class="input-row animate-fade-in">
                <td class="ps-3">
                    <input type="number" name="details[${rowIndex}][jumlah]" class="form-control" min="1" placeholder="1" required>
                </td>
                <td>
                    <select name="details[${rowIndex}][satuan]" class="form-select">
                        <option value="Unit">Unit</option>
                        <option value="Pcs">Pcs</option>
                        <option value="Set">Set</option>
                        <option value="Buah">Buah</option>
                    </select>
                </td>
                <td>
                    <select name="details[${rowIndex}][kondisi]" class="form-select bg-white" required>
                        <option value="Baik">✅ Baik</option>
                        <option value="Rusak Ringan">⚠️ Rusak Ringan</option>
                        <option value="Rusak Berat">❌ Rusak Berat</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="details[${rowIndex}][penanggung_jawab]" class="form-control" placeholder="Nama PJ" required>
                </td>
                <td>
                    <input type="text" name="details[${rowIndex}][keterangan]" class="form-control" placeholder="Opsional">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" style="width:32px; height:32px;" onclick="removeRow(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;

        container.insertAdjacentHTML('beforeend', newRow);
        rowIndex++;
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
    }
</script>

<style>
    /* ------------------------------------------------------------------
       TEMA UNGU MUNZALAN (Updated CSS)
    ------------------------------------------------------------------ */

    .text-purple { color: #883C8C !important; }
    .text-purple-dark { color: #5A1968 !important; }
    .letter-spacing-1 { letter-spacing: 1px; }

    /* HERO CARD */
    .inv-hero-card {
        /* Gradient Ungu Munzalan */
        background: radial-gradient(circle at top left, #a45eb9 0, #883C8C 40%, #5A1968 90%);
        border-radius: 24px;
        padding: 18px 20px;
        color: #F8F2F8;
        box-shadow: 0 18px 40px rgba(90, 25, 104, 0.35);
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
        background: radial-gradient(circle, rgba(230, 215, 233, 0.5), transparent);
    }

    .inv-hero-icon {
        width: 48px;
        height: 48px;
        border-radius: 18px;
        background: rgba(45, 13, 52, 0.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        box-shadow: 0 10px 26px rgba(45, 13, 52, 0.4);
        color: #F8F2F8;
    }

    .inv-hero-kicker { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; font-weight: 600; opacity: .9; }
    .inv-hero-title { font-size: 21px; font-weight: 700; letter-spacing: -0.03em; }
    .inv-hero-subtitle { font-size: 13px; opacity: .96; }

    .inv-badge-soft {
        display: inline-flex; align-items: center; padding: 5px 12px;
        font-size: 11px; border-radius: 999px;
        border: 1px solid rgba(226, 232, 240, 0.7); backdrop-filter: blur(6px);
    }

    .inv-badge-purple {
        background: rgba(136, 60, 140, 0.3); color: #F8F2F8; border: 1px solid rgba(230, 215, 233, 0.4);
    }

    .inv-hero-muted { color: rgba(248, 242, 248, 0.8); }

    /* FORM CARD */
    .inv-form-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #E6D7E9;
        box-shadow: 0 18px 40px rgba(90, 25, 104, 0.06);
        overflow: hidden;
    }

    .inv-form-header {
        padding: 16px 20px;
        border-bottom: 1px solid #E6D7E9;
        background: linear-gradient(135deg, #fdf4ff, #f3e8ff);
    }

    .inv-form-icon {
        width: 36px; height: 36px; border-radius: 12px;
        background: linear-gradient(135deg, #d8b4fe, #883C8C);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }

    .inv-form-body { padding: 24px; }
    .inv-form-divider { border-color: #E6D7E9; margin: 22px 0; }
    .inv-field-hint { font-size: 11px; color: #883C8C; margin-top: 4px; opacity: 0.8; }
    .inv-btn-save { border-radius: 999px; font-weight: 600; }

    /* INPUT STYLING (Ungu Focus) */
    .inv-form-body .form-control,
    .inv-form-body .form-select {
        border-radius: 10px; border-color: #E6D7E9;
        font-size: 14px; padding: 10px 14px; transition: all 0.2s;
    }

    .inv-form-body .form-control:focus,
    .inv-form-body .form-select:focus {
        border-color: #883C8C;
        box-shadow: 0 0 0 3px rgba(136, 60, 140, 0.15);
    }

    /* FIX INPUT GROUP RADIUS */
    .inv-form-body .input-group .form-control { border-radius: 10px 0 0 10px; }
    .inv-form-body .input-group .input-group-text {
        border-radius: 0 10px 10px 0; background-color: #fdf4ff; border-color: #E6D7E9; color: #5A1968;
    }
    
    .inv-form-body .input-group .input-group-text:first-child {
        border-radius: 10px 0 0 10px; border-right: 0;
    }
    .inv-form-body .input-group .form-control:last-child {
        border-radius: 0 10px 10px 0; border-left: 0;
    }
    .inv-form-body .input-group .form-control:focus + .input-group-text,
    .inv-form-body .input-group .input-group-text + .form-control:focus {
        border-color: #883C8C; z-index: 3;
    }
    .btn-dashed { background-color: #fcfaff; border: 2px dashed #d8b4fe; color: #883C8C; transition: all 0.3s ease; border-radius: 12px; font-weight: 600; }
    .btn-dashed:hover { background-color: #f3e8ff; border-color: #883C8C; color: #5A1968; }

    /* BREADCRUMB */
    .breadcrumb { margin-bottom: 0; }
    .breadcrumb-item+.breadcrumb-item::before { content: "›"; color: #E6D7E9; }
    
    @media (max-width: 768px) {
        .inv-hero-card { padding: 16px; }
        .inv-hero-title { font-size: 18px; }
    }
</style>
@endsection