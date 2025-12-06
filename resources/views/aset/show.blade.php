<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aset - {{ $scannedAsset->nama_barang }}</title>

    {{-- CDN Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #F3F4F6; /* Abu lembut agar nyaman di mata */
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .card-custom {
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: white;
        }

        .header-bg {
            background: linear-gradient(135deg, #a45eb9 0%, #5A1968 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
            position: relative;
        }

        .header-bg::after {
            content: '';
            position: absolute;
            bottom: -20px; left: 0; right: 0;
            height: 40px;
            background: white;
            border-radius: 24px 24px 0 0;
        }

        .icon-circle {
            width: 80px; height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .stat-box {
            background: #F8F9FA;
            border: 1px solid #E9ECEF;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            height: 100%;
        }

        .text-purple { color: #883C8C !important; }
        .btn-purple { background-color: #883C8C; color: white; border: none; }
        .btn-purple:hover { background-color: #5A1968; color: white; }
    </style>
</head>
<body>

    <div class="container" style="max-width: 500px;">
        
        <div class="card card-custom border-0">
            
            {{-- HEADER (NAMA & KODE) --}}
            <div class="header-bg mb-3">
                <div class="icon-circle">
                    <i class="fas fa-box-open fa-3x text-white"></i>
                </div>
                <h4 class="fw-bold mb-1">{{ $summary['nama_barang'] }}</h4>
                <div class="badge bg-white text-dark bg-opacity-90 px-3 py-1 rounded-pill fw-bold font-monospace shadow-sm">
                    {{ $summary['kode_aset'] }}
                </div>
            </div>

            <div class="card-body px-4 pb-5 pt-2">
                
                {{-- INFO STOK UTAMA --}}
                <div class="text-center mb-4">
                    <small class="text-uppercase text-muted fw-bold ls-1">Total Stok Fisik</small>
                    @if($summary['total_stok'] > 0)
                        <div class="display-4 fw-bold text-dark lh-1 my-1">{{ $summary['total_stok'] }}</div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill">Tersedia</span>
                    @else
                        <div class="display-4 fw-bold text-danger lh-1 my-1">0</div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill">Habis / Musnah</span>
                    @endif
                </div>

                {{-- INFO SERVICE (ALERT) --}}
                @if($summary['sedang_servis'] > 0)
                <div class="alert alert-primary d-flex align-items-center border-0 shadow-sm rounded-3 mb-4 bg-primary bg-opacity-10 text-primary py-2 px-3">
                    <i class="fas fa-tools fa-lg me-3"></i>
                    <div class="small lh-sm">
                        <strong>Sedang Diservis:</strong> {{ $summary['sedang_servis'] }} unit.
                        <br>Barang ini sedang dalam perbaikan.
                    </div>
                </div>
                @endif

                {{-- DETAIL RINCIAN (GRID 3 KOLOM) --}}
                <div class="row g-2 mb-4">
                    <div class="col-4">
                        <div class="stat-box">
                            <div class="fw-bold text-success fs-4">{{ $summary['stok_baik'] }}</div>
                            <div class="small text-muted fw-bold" style="font-size: 10px;">BAIK</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <div class="fw-bold text-warning fs-4">{{ $summary['stok_rusak_ringan'] }}</div>
                            <div class="small text-muted fw-bold" style="font-size: 10px;">RUSAK RINGAN</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <div class="fw-bold text-danger fs-4">{{ $summary['stok_rusak_berat'] }}</div>
                            <div class="small text-muted fw-bold" style="font-size: 10px;">RUSAK BERAT</div>
                        </div>
                    </div>
                </div>

                {{-- INFO LIST --}}
                <ul class="list-group list-group-flush rounded-3 mb-4 border" style="font-size: 13px;">
                    <li class="list-group-item d-flex justify-content-between py-3">
                        <span class="text-muted"><i class="fas fa-tags me-2 text-purple"></i>Kategori</span>
                        <span class="fw-bold text-dark">{{ $summary['kategori'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-3">
                        <span class="text-muted"><i class="fas fa-map-marker-alt me-2 text-purple"></i>Lokasi</span>
                        <span class="fw-bold text-dark">{{ $summary['lokasi_utama'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-3">
                        <span class="text-muted"><i class="fas fa-user-shield me-2 text-purple"></i>Penanggung Jawab</span>
                        <span class="fw-bold text-dark">{{ $summary['pj_utama'] }}</span>
                    </li>
                </ul>

                {{-- TOMBOL AKSI --}}
                <div class="d-grid gap-2">
                    @auth
                        {{-- Jika Admin/Ustad Login --}}
                        <a href="{{ route('assets.index') }}" class="btn btn-purple py-2 fw-bold shadow-sm rounded-pill">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                        </a>
                        
                        @if(Auth::user()->role_user == 'admin')
                            {{-- Admin bisa langsung edit dari sini --}}
                            <a href="{{ route('assets.edit', $scannedAsset->id) }}" class="btn btn-outline-warning text-dark py-2 fw-bold rounded-pill border-warning">
                                <i class="fas fa-edit me-1"></i> Edit Data Ini
                            </a>
                        @endif
                    @else
                        {{-- Jika Publik/Tamu --}}
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm py-2 rounded-pill">
                            <i class="fas fa-lock me-1"></i> Login
                        </a>
                    @endauth
                </div>

                <div class="mt-4 text-center text-muted small opacity-50">
                    &copy; {{ date('Y') }} Sistem Aset Munzalan
                </div>

            </div>
        </div>
    </div>

</body>
</html>