<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aset - {{ $asset->nama_barang }}</title>

    {{-- CDN Bootstrap 5 (Agar tampilan tetap bagus) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #F5F0F6; /* Background ungu muda pudar */
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Warna Tema (Disamakan dengan Admin) */
        .text-purple { color: #883C8C !important; }
        .text-purple-dark { color: #2D0D34 !important; }
        .bg-purple-subtle { background-color: #E6D7E9 !important; color: #5A1968 !important; }

        .card-custom {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(90, 25, 104, 0.1);
            overflow: hidden;
        }
        
        .brand-logo {
            width: 60px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                {{-- Kartu Aset --}}
                <div class="card card-custom border-0 bg-white">
                    <div class="card-body text-center p-5">
                        
                        {{-- Logo Yayasan (Opsional: Jika ada file logo di public) --}}
                        {{-- <img src="{{ asset('images/logo.png') }}" class="brand-logo" alt="Logo"> --}}
                        
                        <h6 class="text-muted text-uppercase small ls-1 mb-4">Informasi Detail Aset</h6>
                        
                        {{-- QR Code (Hanya Gambar, tanpa link balik ke diri sendiri agar bersih) --}}
                        <div class="mb-4 d-flex justify-content-center">
                            <div class="p-2 bg-white rounded border">
                                <img src="{{ (new \chillerlan\QRCode\QRCode)->render(route('assets.show', $asset->id)) }}" 
                                     alt="QR Code" style="width: 140px; height: auto;">
                            </div>
                        </div>

                        <h2 class="fw-bold text-purple-dark mb-1">{{ $asset->nama_barang }}</h2>
                        <div class="badge bg-purple-subtle mb-4 fs-6 px-3 py-2 rounded-pill">
                            {{ $asset->kode_aset }}
                        </div>

                        {{-- List Detail --}}
                        <ul class="list-group list-group-flush text-start rounded-3 mb-4" style="background-color: #fcfaff;">
                            <li class="list-group-item bg-transparent d-flex justify-content-between py-3 border-bottom-0">
                                <span class="text-muted"><i class="fas fa-info-circle me-2 text-purple"></i>Kondisi</span>
                                @if($asset->kondisi == 'Baik')
                                    <span class="badge bg-success rounded-pill">Baik</span>
                                @elseif($asset->kondisi == 'Rusak Ringan')
                                    <span class="badge bg-warning text-dark rounded-pill">Rusak Ringan</span>
                                @else
                                    <span class="badge bg-danger rounded-pill">Rusak Berat</span>
                                @endif
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between py-3 border-bottom-0">
                                <span class="text-muted"><i class="fas fa-map-marker-alt me-2 text-purple"></i>Lokasi</span>
                                <span class="fw-bold text-dark">{{ $asset->lokasi }}</span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between py-3 border-bottom-0">
                                <span class="text-muted"><i class="fas fa-user-shield me-2 text-purple"></i>PJ</span>
                                <span class="fw-bold text-dark">{{ $asset->penanggung_jawab }}</span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between py-3">
                                <span class="text-muted"><i class="fas fa-layer-group me-2 text-purple"></i>Stok</span>
                                <span class="fw-bold text-dark">{{ $asset->jumlah }} {{ $asset->satuan }}</span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            {{-- Jika user LOGIN (Admin), tampilkan tombol Edit/Kembali --}}
                            @auth
                                <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning text-dark fw-bold">
                                    <i class="fas fa-edit me-1"></i> Edit Data
                                </a>
                                <a href="{{ route('assets.index') }}" class="btn btn-light text-purple fw-bold">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Admin
                                </a>
                            @else
                                {{-- Jika PUBLIK (Scan HP), tampilkan tombol Login --}}
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                    <i class="fas fa-lock me-1"></i> Login Petugas
                                </a>
                            @endauth
                        </div>

                        <div class="mt-4 text-muted small">
                            &copy; {{ date('Y') }} Sistem Aset Munzalan
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>