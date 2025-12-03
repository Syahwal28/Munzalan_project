@extends('layouts.app')

@section('page-title', 'Daftar Perbaikan Aset')

@section('content')

<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom-purple-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-purple-dark">
            <i class="fas fa-tools me-2 text-purple"></i> Monitoring Service
        </h5>
        <a href="{{ route('perbaikan.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Input Kerusakan Baru
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-purple-light">
                    <tr>
                        <th class="ps-4 text-purple-dark">Tgl Masuk</th>
                        <th class="text-purple-dark">Barang</th>
                        <th class="text-purple-dark">Jumlah</th>
                        <th class="text-purple-dark">Kerusakan</th>
                        <th class="text-center text-purple-dark">Status</th>
                        <th class="text-end pe-4 text-purple-dark">Biaya & Nota</th>
                        <th class="text-center text-purple-dark">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perbaikan as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $item->tgl_masuk }}</div>
                            <small class="text-muted">{{ $item->penanggung_jawab }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-purple-dark">{{ $item->aset->nama_barang ?? 'Dihapus' }}</div>
                            <small class="text-muted">{{ $item->aset->kode_aset ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                {{ $item->jumlah_perbaikan }}
                            </span>
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
                                {{-- Tombol Trigger Modal --}}
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalSelesai{{ $item->id }}">
                                    <i class="fas fa-check me-1"></i> Selesaikan
                                </button>
                            @else
                                <button class="btn btn-sm btn-light text-muted" disabled><i class="fas fa-check-circle"></i></button>
                            @endif
                        </td>
                    </tr>
                    <!-- Modal Selesaikan Perbaikan -->
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
                                        <p class="small text-muted mb-3">
                                            Barang: <strong>{{ $item->aset->nama_barang }}</strong><br>
                                            Kerusakan: {{ $item->keterangan_kerusakan }}
                                        </p>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Tanggal Selesai</label>
                                            <input type="date" name="tgl_selesai" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Biaya Perbaikan (Sesuai Nota)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" name="biaya" class="form-control" placeholder="0" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Upload Bukti Nota</label>
                                            <input type="file" name="bukti_nota" class="form-control" accept="image/*">
                                            <div class="form-text small">Format: JPG, PNG (Max 2MB)</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Keterangan Perbaikan</label>
                                            <textarea name="keterangan_perbaikan" class="form-control" rows="2" placeholder="Apa yang diperbaiki/diganti?"></textarea>
                                        </div>

                                        {{-- HASIL PERBAIKAN --}}
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-purple-dark">Hasil Perbaikan / Kondisi Akhir</label>
                                            <select name="kondisi_akhir" id="kondisiAkhir{{ $item->id }}" class="form-select border-purple-light" 
                                                    onchange="toggleInputGagal({{ $item->id }})" required>
                                                <option value="">-- Bagaimana kondisi barang sekarang? --</option>
                                                <option value="Baik">✅ Semua Berhasil Diperbaiki (Stok Kembali Baik)</option>
                                                <option value="Rusak Berat">❌ Ada yang Gagal / Tidak Bisa Diperbaiki</option>
                                            </select>
                                        </div>
                                        {{-- INPUT JUMLAH GAGAL (Hidden by default) --}}
                                        <div id="boxJumlahGagal{{ $item->id }}" class="mb-3 d-none animate-fade-in p-3 bg-danger bg-opacity-10 rounded border border-danger">
                                            <label class="form-label fw-bold small text-danger">Berapa unit yang Gagal / Rusak Berat?</label>
                                            <input type="number" name="jumlah_gagal" class="form-control border-danger text-danger fw-bold" 
                                                min="1" max="{{ $item->jumlah_perbaikan }}" placeholder="Jumlah Gagal">
                                            <div class="form-text text-danger small">
                                                Sisa unit lainnya ({{ $item->jumlah_perbaikan }} - Gagal) otomatis dianggap <strong>Baik</strong>.
                                                <br>Barang gagal akan otomatis masuk ke <strong>Log Aset Rusak</strong>.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan & Selesai</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Belum ada data perbaikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $perbaikan->links() }}
        </div>
    </div>
</div>

<!-- Script untuk field jumlah item rusak (hidden) -->
<script>
    function toggleInputGagal(id) {
        var select = document.getElementById('kondisiAkhir' + id);
        var box = document.getElementById('boxJumlahGagal' + id);
        var input = box.querySelector('input');

        if (select.value === 'Rusak Berat') {
            box.classList.remove('d-none');
            input.required = true;
        } else {
            box.classList.add('d-none');
            input.required = false;
            input.value = '';
        }
    }
</script>
<style>
    .bg-purple-light { background-color: #F5F0F6; }
    .text-purple { color: #883C8C; }
    .text-purple-dark { color: #2D0D34; }
    .border-bottom-purple-light { border-bottom: 1px solid #E6D7E9; }
</style>
@endsection