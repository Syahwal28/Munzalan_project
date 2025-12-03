@extends('layouts.app')

@section('page-title', 'Log Penghapusan Aset')

@section('content')

<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom-purple-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-purple-dark">
            <i class="fas fa-archive me-2 text-purple"></i> Daftar Aset Rusak Berat / Musnah
        </h5>
        <a href="{{ route('transaksi.create') }}" class="btn btn-sm btn-danger shadow-sm">
            <i class="fas fa-minus-circle me-1"></i> Catat Aset Rusak
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-purple-light">
                    <tr>
                        <th class="ps-4 text-purple-dark">Tanggal Catat</th>
                        <th class="text-purple-dark">Barang / Aset</th>
                        <th class="text-purple-dark">Keterangan Kerusakan</th>
                        <th class="text-center text-purple-dark">Jumlah Dimusnahkan</th>
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
                                    <small class="text-muted">{{ $item->aset->kode_aset ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{-- Kita gunakan kolom 'alasan' untuk menyimpan detail kerusakan --}}
                            <span class="text-muted fst-italic">{{ $item->alasan }}</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger fs-5">-{{ $item->jumlah_keluar }}</span>
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

        <div class="px-4 py-3 border-top-purple-light">
            {{ $transaksi->links() }}
        </div>
    </div>
</div>

<style>
    .bg-purple-light { background-color: #F5F0F6; }
    .text-purple { color: #883C8C; }
    .text-purple-dark { color: #2D0D34; }
    .border-bottom-purple-light { border-bottom: 1px solid #E6D7E9; }
    .avatar-sm { width: 35px; height: 35px; }
</style>
@endsection