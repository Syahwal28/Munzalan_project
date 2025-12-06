@extends('layouts.app')

@section('page-title', 'Edit Data Aset')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('assets.index') }}" class="text-decoration-none text-muted">Data Aset</a></li>
                <li class="breadcrumb-item active text-purple fw-bold">Edit Aset</li>
            </ol>
        </nav>

        {{-- Hero Card Edit --}}
        <div class="inv-hero-card d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="inv-hero-icon"><i class="fas fa-edit"></i></div>
                <div>
                    <div class="inv-hero-kicker mb-1">Mode Perubahan Data</div>
                    <h2 class="inv-hero-title mb-1">Edit Aset: {{ $asset->nama_barang }}</h2>
                    <p class="inv-hero-subtitle mb-0">No Inventaris: {{ $asset->kode_aset }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="inv-form-card">
            <div class="inv-form-header d-flex align-items-center gap-3">
                <div class="inv-form-icon"><i class="fas fa-pen"></i></div>
                <div>
                    <h5 class="mb-1 fw-bold text-purple-dark">Form Perubahan Data</h5>
                    <p class="mb-0 small text-muted">Ubah data aset utama atau rincian fisiknya.</p>
                </div>
            </div>

            <div class="inv-form-body">
                {{-- Kita kirim ID salah satu varian, tapi di controller nanti kita update by Kode Aset --}}
                <form action="{{ route('assets.update', $asset->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- === BAGIAN 1: INFORMASI UMUM === --}}
                    <h6 class="text-uppercase text-purple fw-bold small mb-3 letter-spacing-1">
                        <i class="fas fa-info-circle me-1"></i> Informasi Umum
                    </h6>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-purple-dark">No Inventaris</label>
                            <input type="text" name="kode_aset" class="form-control bg-light" value="{{ old('kode_aset', $asset->kode_aset) }}" readonly>
                            <div class="inv-field-hint">No Inventaris tidak dapat diubah (Kunci Utama).</div>
                        </div>
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-purple-dark">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control" value="{{ old('nama_barang', $asset->nama_barang) }}" required>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label fw-semibold text-purple-dark">Kategori</label>
                            <select name="kategori" class="form-select">
                                <option value="Elektronik" {{ $asset->kategori == 'Elektronik' ? 'selected' : '' }}>Elektronik</option>
                                <option value="Furniture" {{ $asset->kategori == 'Furniture' ? 'selected' : '' }}>Furniture</option>
                                <option value="Lahan & Bangunan" {{ $asset->kategori == 'Lahan & Bangunan' ? 'selected' : '' }}>Lahan & Bangunan</option>
                                <option value="Kendaraan" {{ $asset->kategori == 'Kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                                <option value="Peralatan" {{ $asset->kategori == 'Peralatan' ? 'selected' : '' }}>Peralatan</option>
                                <option value="Lainnya" {{ $asset->kategori == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-purple-dark">Sumber</label>
                            <select name="sumber_aset" class="form-select">
                                <option value="Yayasan" {{ $asset->sumber_aset == 'Yayasan' ? 'selected' : '' }}>Yayasan</option>
                                <option value="Wakaf" {{ $asset->sumber_aset == 'Wakaf' ? 'selected' : '' }}>Wakaf</option>
                                <option value="Hibah" {{ $asset->sumber_aset == 'Hibah' ? 'selected' : '' }}>Hibah</option>
                                <option value="Beli Sendiri" {{ $asset->sumber_aset == 'Beli Sendiri' ? 'selected' : '' }}>Beli Sendiri</option>
                            </select>
                        </div>
                    </div>

                    <hr class="inv-form-divider">

                    {{-- === BAGIAN 2: RINCIAN FISIK (DYNAMIC ROWS) === --}}
                    {{-- Kita perlu mengambil semua varian data dengan kode aset yang sama --}}
                    @php
                        // Ambil semua varian berdasarkan kode aset ini
                        $variants = \App\Models\AsetModel::where('kode_aset', $asset->kode_aset)->get();
                    @endphp

                    <h6 class="text-purple-dark fw-bold mb-3">
                        <i class="fas fa-boxes me-1"></i> Rincian Jumlah & Kondisi
                    </h6>

                    <div class="table-responsive mb-3 rounded-3 border border-light">
                        <table class="table align-middle mb-0" id="tableRincian">
                            <thead style="background-color: #fdf4ff;">
                                <tr>
                                    <th width="10%" class="text-purple-dark ps-3">Jumlah <span class="text-danger">*</span></th>
                                    <th width="15%" class="text-purple-dark">Satuan</th>
                                    <th width="20%" class="text-purple-dark">Kondisi <span class="text-danger">*</span></th>
                                    <th width="25%" class="text-purple-dark">Penanggung Jawab</th>
                                    <th width="25%" class="text-purple-dark">Ket/Spesifikasi Aset</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="containerInput">
                                {{-- LOOPING DATA VARIAN (HANYA YG STOK > 0) --}}
                                @foreach($variants as $index => $item)
                                    @if($item->jumlah > 0) {{-- Filter Stok 0 (Bug Fix) --}}
                                    <tr class="input-row">
                                        {{-- Hidden Input ID agar controller tahu ini update, bukan create --}}
                                        <input type="hidden" name="details[{{ $index }}][id]" value="{{ $item->id }}">
                                        
                                        <td class="ps-3">
                                            <input type="number" name="details[{{ $index }}][jumlah]" class="form-control" min="1" value="{{ $item->jumlah }}" required>
                                        </td>
                                        <td>
                                            <select name="details[{{ $index }}][satuan]" class="form-select">
                                                <option value="Unit" {{ $item->satuan == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                <option value="Pcs" {{ $item->satuan == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                                <option value="Set" {{ $item->satuan == 'Set' ? 'selected' : '' }}>Set</option>
                                                <option value="Buah" {{ $item->satuan == 'Buah' ? 'selected' : '' }}>Buah</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="details[{{ $index }}][kondisi]" class="form-select bg-white" required>
                                                <option value="Baik" {{ $item->kondisi == 'Baik' ? 'selected' : '' }}>✅ Baik</option>
                                                <option value="Rusak Ringan" {{ $item->kondisi == 'Rusak Ringan' ? 'selected' : '' }}>⚠️ Rusak Ringan</option>
                                                <option value="Rusak Berat" {{ $item->kondisi == 'Rusak Berat' ? 'selected' : '' }}>❌ Rusak Berat</option>
                                            </select>
                                        </td>
                                        <td>
                                            {{-- INPUT PJ PER BARIS --}}
                                            <input type="text" name="details[{{ $index }}][penanggung_jawab]" class="form-control" value="{{ $item->penanggung_jawab }}">
                                        </td>
                                        <td>
                                            <input type="text" name="details[{{ $index }}][keterangan]" class="form-control" value="{{ $item->keterangan }}" placeholder="Ket/Spesifikasi Aset">
                                        </td>
                                        <td class="text-center">
                                            {{-- Tombol Hapus (Tampilkan checkbox hapus atau hidden field delete) --}}
                                            {{-- Sederhana: Kita biarkan user menghapus baris, nanti di controller kita sync --}}
                                            @if($index > 0)
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" style="width:32px; height:32px;" onclick="removeRow(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- === BAGIAN 3: LOKASI & LAINNYA === --}}
                    <div class="row mb-3 p-3 bg-purple-light rounded border border-purple-light">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-purple-dark">Lokasi Penyimpanan (Gudang/Ruangan) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-purple"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="lokasi" class="form-control" placeholder="Cth: Gudang Utama, Ruang Guru" value="{{ old('lokasi', $asset->lokasi) }}"required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-purple-dark">Tanggal Perolehan</label>
                            <input type="date" name="tanggal_perolehan" class="form-control" 
                                   value="{{ old('tanggal_perolehan', $asset->tanggal_perolehan ? $asset->tanggal_perolehan->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-purple-dark">Harga Perolehan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-purple">Rp</span>
                                <input type="number" name="harga_perolehan" class="form-control" value="{{ old('harga_perolehan', $asset->harga_perolehan) }}">
                            </div>
                        </div>
                    </div>

                    <hr class="inv-form-divider">

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-warning text-dark px-4 inv-btn-save shadow-sm">
                            <i class="fas fa-save me-2"></i>Perbarui Data
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
    // Mulai index dari jumlah data yang ada agar name array tidak bentrok
    let rowIndex = {{ count($variants) + 1 }};

    function addRow() {
        const container = document.getElementById('containerInput');
        
        const newRow = `
            <tr class="input-row animate-fade-in">
                {{-- ID kosong menandakan data baru --}}
                <input type="hidden" name="details[${rowIndex}][id]" value="">
                
                <td class="ps-3">
                    <input type="number" name="details[${rowIndex}][jumlah]" class="form-control" min="1" placeholder="Jml" required>
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
                    {{-- INPUT PJ PER BARIS --}}
                    <input type="text" name="details[${rowIndex}][penanggung_jawab]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="details[${rowIndex}][keterangan]" class="form-control" placeholder="Ket/Spesifikasi Aset" required>
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
        // Logika penghapusan baris (hanya visual di sini, backend perlu handle hapus data)
        // Jika data sudah ada di DB (punya ID), kita sebaiknya tandai untuk dihapus (misal pakai hidden input _delete)
        // Tapi untuk simplifikasi, hapus visual dulu.
        btn.closest('tr').remove();
    }
</script>

<style>
    /* STYLE SAMA DENGAN CREATE.BLADE.PHP */
    .text-purple { color: #883C8C !important; }
    .text-purple-dark { color: #5A1968 !important; }
    .bg-purple-light { background-color: #fdf4ff !important; }
    .border-purple-light { border-color: #f0abfc !important; }
    
    .inv-hero-card { 
        background: radial-gradient(circle at top left, #c084fc 0, #7e22ce 40%, #581c87 90%); 
        border-radius: 24px; 
        padding: 18px 20px; 
        color: #F8F2F8; 
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
        background: radial-gradient(circle, rgba(255,255,255,0.2), transparent); 
    }
    .inv-hero-icon { width: 48px; height: 48px; border-radius: 18px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; }
    .inv-hero-kicker { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; font-weight: 600; opacity: .9; }
    .inv-hero-title { font-size: 21px; font-weight: 700; letter-spacing: -0.03em; }
    .inv-hero-subtitle { font-size: 13px; opacity: .96; }

    .inv-form-card { background: #ffffff; border-radius: 20px; border: 1px solid #E6D7E9; overflow: hidden; }
    .inv-form-header { padding: 16px 20px; border-bottom: 1px solid #E6D7E9; background: linear-gradient(135deg, #fdf4ff, #f3e8ff); }
    .inv-form-icon { width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, #d8b4fe, #883C8C); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .inv-form-body { padding: 24px; }
    .inv-form-divider { border-color: #E6D7E9; margin: 22px 0; }
    .inv-field-hint { font-size: 11px; color: #883C8C; margin-top: 4px; opacity: 0.8; }
    .inv-btn-save { border-radius: 999px; font-weight: 600; }

    .btn-dashed { background-color: #fcfaff; border: 2px dashed #d8b4fe; color: #883C8C; transition: all 0.3s ease; border-radius: 12px; font-weight: 600; }
    .btn-dashed:hover { background-color: #f3e8ff; border-color: #883C8C; color: #5A1968; }

    .form-control, .form-select { border-radius: 8px; border-color: #E6D7E9; font-size: 14px; }
    .form-control:focus, .form-select:focus { border-color: #883C8C; box-shadow: 0 0 0 3px rgba(136, 60, 140, 0.15); }
    .breadcrumb { margin-bottom: 0; }
    .breadcrumb-item+.breadcrumb-item::before { content: "›"; color: #E6D7E9; }
    
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection