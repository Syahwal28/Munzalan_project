<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Munzalan Inventory') }}</title>

    {{-- CDN Bootstrap & FontAwesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* --- SKEMA WARNA BARU (MUNZALAN PURPLE) --- */
        :root {
            --primary-color: #883C8C;    /* Ungu Aksen (Sedang) */
            --primary-dark: #5A1968;     /* Ungu Utama (Gelap) */
            --primary-light: #E6D7E9;    /* Ungu Sangat Muda */
            --sidebar-width: 260px;
            --bg-body: #F5F0F6;          /* Latar Belakang Abu-Ungu Muda */
            --text-dark: #2D0D34;        /* Teks Gelap (Ungu Kehitaman) */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            overflow-x: hidden;
            font-size: 14px;
            color: var(--text-dark);
        }

        /* Sidebar Modern */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            max-height: 100vh;
            background: #ffffff;
            color: var(--text-dark);
            box-shadow: 4px 0 20px rgba(90, 25, 104, 0.08); /* Shadow ungu tipis */
            border-right: 1px solid #E6D7E9;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Logo/Brand Area */
        .sidebar-brand {
            padding: 20px 25px;
            border-bottom: 1px solid #E6D7E9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* STYLE BARU UNTUK LOGO GAMBAR */
        .brand-logo-img {
            width: 45px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(90, 25, 104, 0.2));
        }

        .brand-text {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }

        /* User Profile di Sidebar */
        .user-profile {
            padding: 20px;
            background: var(--primary-light);
            margin: 15px;
            border-radius: 12px;
            border: 1px solid #d8c5dd;
        }

        .user-profile-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 12px;
            color: white;
        }

        .user-profile h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .user-profile small {
            font-size: 11px;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Navigation Links */
        .sidebar-nav {
            padding: 10px 15px;
        }

        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 20px 10px 10px;
        }

        .sidebar .nav-link {
            color: #64748b;
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .sidebar .nav-link:hover {
            color: var(--primary-color);
            background: var(--primary-light);
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            box-shadow: 0 4px 12px rgba(136, 60, 140, 0.3);
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 16px;
        }

        /* Submenu Collapse */
        .collapse .nav-link {
            font-size: 13px;
            padding: 10px 16px 10px 48px;
        }

        /* Main Content Area */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
            min-height: 100vh;
            background-color: var(--bg-body);
            transition: margin-left 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 16px 30px;
            box-shadow: 0 1px 3px rgba(90, 25, 104, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #E6D7E9;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 8px 16px 8px 40px;
            border: 1px solid #E6D7E9;
            border-radius: 8px;
            width: 300px;
            font-size: 14px;
            background-color: var(--bg-body);
        }
        .search-box input:focus {
             border-color: var(--primary-color);
             box-shadow: 0 0 0 3px rgba(136, 60, 140, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }

        /* Content Container */
        .content-container {
            padding: 30px;
        }

        /* UTILITIES & COMPONENTS */
        .card-custom {
            background: white;
            border-radius: 16px;
            border: 1px solid #E6D7E9;
            box-shadow: 0 2px 6px rgba(90, 25, 104, 0.04);
            overflow: hidden;
        }
        
        .hero-header {
            margin-bottom: 24px;
        }

        /* Buttons Modern */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(136, 60, 140, 0.3);
            background: linear-gradient(135deg, var(--primary-dark), #3e1147);
        }

        .btn-logout {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        .bg-primary-subtle {
            background-color: var(--primary-light) !important;
        }

        /* Mobile Responsive */
        .mobile-nav {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 5px 0 25px rgba(90, 25, 104, 0.15);
            }

            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }

            .mobile-nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 20px;
                background: white;
                box-shadow: 0 1px 3px rgba(90, 25, 104, 0.1);
                position: fixed;
                top: 0; left: 0; right: 0;
                z-index: 999;
            }

            .mobile-nav-brand {
                font-weight: 700;
                font-size: 18px;
                color: var(--primary-dark);
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .mobile-nav-brand img {
                width: 35px;
            }

            .mobile-toggle {
                background: none;
                border: none;
                font-size: 24px;
                color: var(--primary-dark);
                cursor: pointer;
            }

            .top-navbar {
                display: none;
            }

            .search-box input {
                width: 200px;
            }
        }

        /* Scrollbar Custom */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: var(--bg-body); }
        .sidebar::-webkit-scrollbar-thumb { background: var(--primary-light); border-radius: 3px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: var(--primary-color); }
    </style>
</head>

<body>

    <div class="mobile-nav">
        <div class="mobile-nav-brand">
            {{-- GANTI DENGAN LOGO GAMBAR --}}
            <img src="{{ asset('storage/images/logo.png') }}" alt="Logo Munzalan">
            Munzalan Inv.
        </div>
        <button class="mobile-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav id="sidebarMenu" class="sidebar">
        <div class="sidebar-brand">
            {{-- LOGO GAMBAR DI SIDEBAR --}}
            <img src="{{ asset('storage/images/logo.png') }}" alt="Logo Munzalan" class="brand-logo-img">
            <div class="brand-text">MUNZALAN</div>
        </div>

        <div class="user-profile">
            <div class="user-profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            @auth
                <h6>{{ Auth::user()->nama_user }}</h6> 
                <small>{{ ucfirst(Auth::user()->role_user) }}</small>
            @else
                <h6>Tamu</h6>
            @endauth
        </div>

        <div class="sidebar-nav">
            <div class="nav-section-title">Menu Utama</div>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a>

            <a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.index') || request()->routeIs('assets.edit') ? 'active' : '' }}">
                <i class="fas fa-cubes"></i> Data Aset
            </a>

            <div class="nav-section-title">Administrasi</div>

            <a class="nav-link" data-bs-toggle="collapse" href="#menuTransaksi" role="button">
                <i class="fas fa-exchange-alt"></i> Transaksi
                <i class="fas fa-chevron-down ms-auto" style="font-size: 10px;"></i>
            </a>
            <div class="collapse" id="menuTransaksi">

                <!-- Menu barang keluar -->
                <a href="{{ route('transaksi.index') }}" class="nav-link {{ request()->routeIs('transaksi.*') ? 'active' : '' }}">
                    <i class="fas fa-sign-out-alt"></i> Aset Rusak
                </a>

                <!-- Menu daftar perbaikan -->
                <a href="{{ route('perbaikan.index') }}" class="nav-link {{ request()->routeIs('perbaikan.*') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i> Daftar Perbaikan
                </a>
            </div>

            <div style="padding: 20px 10px; margin-top: 30px;">
                @auth
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-logout w-100">
                        <i class="fas fa-sign-out-alt me-2"></i> Keluar
                    </button>
                </form>
                @else
                <a href="#" class="btn btn-primary w-100 text-white" style="text-decoration: none;">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="top-navbar">
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            <div class="navbar-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari aset...">
                </div>
            </div>
        </div>

        <div class="content-container">
            {{-- Flash Message (Alert Sukses) - Disesuaikan warnanya --}}
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="background-color: #d1fae5; color: #065f46;">
                    <i class="fas fa-check-circle me-2 fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebarMenu').classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebarMenu');
            const toggle = document.querySelector('.mobile-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
</body>

</html>