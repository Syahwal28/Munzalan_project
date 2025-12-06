@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')

<style>
    /* =========================
       DASHBOARD HEADER (TEMA UNGU)
    ========================== */
    .dashboard-header {
        /* Gradient Ungu Munzalan */
        background: radial-gradient(circle at top left, #d8b4fe 0, #883C8C 35%, #5A1968 85%);
        border-radius: 24px;
        padding: 24px 28px;
        color: #fce7ff;
        box-shadow: 0 18px 40px rgba(90, 25, 104, 0.4);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        right: -40px;
        top: -40px;
        width: 160px;
        height: 160px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2), transparent);
        opacity: 0.9;
    }

    .dashboard-header-icon {
        width: 64px;
        height: 64px;
        border-radius: 22px;
        background: rgba(45, 13, 52, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 28px rgba(45, 13, 52, 0.2);
    }

    .dashboard-header-icon i {
        font-size: 30px;
        color: #ffffff;
    }

    .dashboard-header-chip {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        font-weight: 600;
        opacity: 0.92;
        color: #E6D7E9;
    }

    .dashboard-header-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: white;
    }

    .dashboard-header-subtitle {
        font-size: 13px;
        opacity: 0.95;
        color: #f3e8f5;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 500;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(8px);
        color: #ffffff;
    }

    /* =========================
       STAT MINI CARDS
    ========================== */
    .stat-card-mini {
        padding: 18px 18px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid rgba(136, 60, 140, 0.1);
        box-shadow: 0 10px 30px rgba(90, 25, 104, 0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card-mini:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 45px rgba(90, 25, 104, 0.1);
        border-color: #883C8C;
    }

    .stat-label-mini {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .stat-value-mini {
        font-size: 28px;
        font-weight: 700;
        color: #2D0D34;
        line-height: 1.1;
    }

    .stat-caption {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
    }

    .stat-icon-mini {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stretched-link::after {
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        z-index: 1;
        content: "";
    }

    /* =========================
       SECTION CARD WRAPPER
    ========================== */
    .section-card {
        background: #ffffff;
        border-radius: 22px;
        padding: 20px 22px;
        border: 1px solid rgba(136, 60, 140, 0.15);
        box-shadow: 0 16px 40px rgba(90, 25, 104, 0.02);
    }

    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #2D0D34;
        letter-spacing: -0.01em;
    }

    .section-subtitle {
        font-size: 12px;
        color: #64748b;
    }

    .badge-soft-info {
        background: #eff6ff; color: #1d4ed8;
        border: 1px solid rgba(59, 130, 246, 0.25);
        padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 600;
    }
    
    .badge-soft-purple {
        background: #f3e8f5; color: #883C8C;
        border: 1px solid rgba(136, 60, 140, 0.25);
        padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 600;
    }

    /* =========================
       MODERN TABLE
    ========================== */
    .modern-table {
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(136, 60, 140, 0.1);
    }

    .modern-table table { margin-bottom: 0; font-size: 13px; }
    .modern-table thead { background: #F5F0F6; }
    
    .modern-table thead th {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.05em; color: #5A1968;
        border-bottom: 1px solid #E6D7E9; padding: 14px 16px;
    }

    .modern-table tbody td {
        padding: 12px 16px; vertical-align: middle;
        border-bottom: 1px solid #f8f1f9; color: #334155;
    }
    
    .inv-empty-state { padding: 2rem; text-align: center; }
    .inv-empty-icon {
        width: 52px; height: 52px; border-radius: 50%;
        background: #F5F0F6; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 10px; color: #883C8C; font-size: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header { padding: 18px; }
        .dashboard-header-icon { width: 56px; height: 56px; }
        .stat-value-mini { font-size: 24px; }
    }
</style>

{{-- HEADER DASHBOARD --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="dashboard-header-icon">
                    <i class="fas fa-mosque"></i>
                </div>
                <div>
                    <div class="dashboard-header-chip mb-1">
                        Munzalan Inventory
                    </div>
                    <h2 class="dashboard-header-title mb-1">
                        Ahlan Wa Sahlan, {{ Auth::user()->nama_user }} 👋
                    </h2>
                    <p class="dashboard-header-subtitle mb-0">
                        Pantau seluruh aset yayasan dalam satu tampilan terpadu.
                    </p>
                </div>
            </div>

            <div class="text-end d-none d-md-block">
                <span class="badge-soft mb-2">
                    <i class="fas fa-user-shield me-1"></i>
                    {{ Auth::user()->role_user ?? 'Admin Yayasan' }}
                </span>
                <div class="small text-white opacity-75">
                    <i class="far fa-clock me-1"></i>
                    {{ date('d F Y') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- STATISTIK MINI CARDS --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Total Aset (Ungu) --}}
    <div class="col-6 col-md-3">
        <div class="stat-card-mini" style="background: linear-gradient(135deg, #f3e8f5, #fce7ff); border-left: 4px solid #883C8C;">
            <div>
                <div class="stat-label-mini">Jenis Aset</div>
                <div class="stat-value-mini">{{ number_format($totalJenisAset, 0, ',', '.') }}</div>
                <div class="stat-caption">Item terdaftar</div>
            </div>
            <div class="stat-icon-mini" style="background: rgba(136,60,140,0.15); color:#5A1968;">
                <i class="fas fa-cubes"></i>
            </div>
            <a href="{{ route('assets.index') }}" class="stretched-link"></a>
        </div>
    </div>

    {{-- Card 2: Total Stok (Biru) --}}
    <div class="col-6 col-md-3">
        <div class="stat-card-mini" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-left: 4px solid #3b82f6;">
            <div>
                <div class="stat-label-mini">Total Stok</div>
                <div class="stat-value-mini">{{ number_format($totalStok, 0, ',', '.') }}</div>
                <div class="stat-caption">Unit tersedia</div>
            </div>
            <div class="stat-icon-mini" style="background: rgba(59,130,246,0.15); color:#1d4ed8;">
                <i class="fas fa-layer-group"></i>
            </div>
            <a href="{{ route('assets.index') }}" class="stretched-link"></a>
        </div>
    </div>

    {{-- Card 3: Kondisi Baik (Hijau) --}}
    <div class="col-6 col-md-3">
        <div class="stat-card-mini" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-left: 4px solid #10b981;">
            <div>
                <div class="stat-label-mini">Kondisi Baik</div>
                <div class="stat-value-mini text-success">{{ number_format($stokBaik, 0, ',', '.') }}</div>
                <div class="stat-caption">Siap digunakan</div>
            </div>
            <div class="stat-icon-mini" style="background: rgba(16,185,129,0.15); color:#047857;">
                <i class="fas fa-check-circle"></i>
            </div>
             <a href="{{ route('assets.index') }}" class="stretched-link"></a>
        </div>
    </div>

    {{-- Card 4: Rusak (Merah) --}}
    <div class="col-6 col-md-3">
        <div class="stat-card-mini" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-left: 4px solid #ef4444;">
            <div>
                <div class="stat-label-mini">Jumlah Rusak</div>
                <div class="stat-value-mini text-danger">{{ number_format($stokRusak, 0, ',', '.') }}</div>
                <div class="stat-caption">Rusak berat / Dimusnahkan</div>
            </div>
            <div class="stat-icon-mini" style="background: rgba(239,68,68,0.15); color:#b91c1c;">
                <i class="fas fa-tools"></i>
            </div>
             <a href="{{ route('assets.index') }}" class="stretched-link"></a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- BAGIAN KIRI: RINGKASAN STATUS --}}
    {{-- Jika user bukan admin, lebarkan kolom ini jadi full agar rapi --}}
    <div class="{{ Auth::user()->role_user == 'admin' ? 'col-lg-8' : 'col-12' }}">
        <div class="section-card h-100 position-relative overflow-hidden">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="section-title mb-1">Status Inventaris</div>
                    <div class="section-subtitle">
                        Pemantauan kesehatan aset yayasan secara real-time.
                    </div>
                </div>
                <span class="badge-soft-purple">
                    <i class="fas fa-chart-pie me-1"></i> Monitoring
                </span>
            </div>

            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mt-4">
                <div>
                    <div class="text-muted small mb-1 text-uppercase fw-bold ls-1">Rasio Kerusakan</div>
                    <div class="fw-bold" style="font-size: 36px; color: #5A1968; line-height: 1;">
                        {{ $totalStok > 0 ? round(($stokRusak / $totalStok) * 100, 1) : 0 }}%
                    </div>
                    <div class="small text-muted mt-2">
                        <i class="fas fa-info-circle text-purple me-1"></i>
                        Persentase barang rusak dari total unit.
                    </div>
                </div>

                <div class="d-none d-md-block">
                    <i class="fas fa-chart-line fa-5x text-purple opacity-10"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN KANAN: AKSI CEPAT (HANYA UNTUK ADMIN) --}}
    @if(Auth::user()->role_user == 'admin')
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="section-title mb-1">Aksi Cepat</div>
                    <div class="section-subtitle">Shortcut administrasi.</div>
                </div>
                <span class="badge-soft-info">
                    <i class="fas fa-bolt me-1"></i> Admin
                </span>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('assets.create') }}"
                   class="btn btn-outline-primary btn-sm d-flex justify-content-between align-items-center py-2"
                   style="border-radius: 999px; border-color: #883C8C; color: #5A1968;">
                    <span><i class="fas fa-plus me-2"></i> Tambah Aset</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>

                <a href="{{ route('transaksi.create') }}"
                   class="btn btn-outline-warning text-dark btn-sm d-flex justify-content-between align-items-center py-2"
                   style="border-radius: 999px;">
                    <span><i class="fas fa-sign-out-alt me-2"></i> Input Aset Rusak</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>

                <a href="{{ route('transaksi.index') }}"
                   class="btn btn-outline-success btn-sm d-flex justify-content-between align-items-center py-2"
                   style="border-radius: 999px;">
                    <span><i class="fas fa-history me-2"></i> Lihat Riwayat</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- TABEL TRANSAKSI TERBARU --}}
<div class="row g-3">
    <div class="col-12">
        <div class="section-card inv-latest-card p-0 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center p-4 pb-3 border-bottom border-light">
                <div>
                    <div class="section-title mb-1">Aktivitas Terakhir</div>
                    <div class="section-subtitle">
                        5 Transaksi barang keluar terbaru.
                    </div>
                </div>
                <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-light rounded-pill border fw-bold text-muted">
                    Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="modern-table table-responsive border-0 rounded-0">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Barang</th>
                            <th>Status / Alasan</th>
                            <th>Jumlah</th>
                            <th class="text-end pe-4">Penerima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksiTerbaru as $log)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border">
                                    {{ $log->tanggal_keluar->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $log->aset->nama_barang ?? 'Aset Dihapus' }}
                                </div>
                                <div class="small text-muted">{{ $log->aset->kode_aset ?? '-' }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($log->alasan) {
                                        'Rusak', 'Hilang' => 'bg-danger',
                                        'Pemakaian' => 'bg-info text-dark',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }} rounded-pill">{{ $log->alasan }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger">{{ $log->jumlah_keluar }}</span>
                                <small class="text-muted"> {{ $log->aset->satuan ?? '' }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <span class="text-muted small">
                                    {{ $log->penerima ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="inv-empty-state">
                                    <div class="inv-empty-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <p class="mb-0 text-muted">Belum ada transaksi keluar.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection