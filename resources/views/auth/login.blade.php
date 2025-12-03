<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Munzalan Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F0F6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(#E6D7E9 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(90, 25, 104, 0.1);
            overflow: hidden;
            border: 1px solid #E6D7E9;
        }

        .login-header {
            background: radial-gradient(circle at top left, #d8b4fe 0, #883C8C 35%, #5A1968 85%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            backdrop-filter: blur(5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .logo-circle img {
            width: 50px;
            height: auto;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-control {
            background-color: #F8F6F9;
            border: 1px solid #E6D7E9;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 14px;
        }

        .form-control:focus {
            background-color: white;
            border-color: #883C8C;
            box-shadow: 0 0 0 4px rgba(136, 60, 140, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #883C8C, #5A1968);
            border: none;
            color: white;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(136, 60, 140, 0.3);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="logo-circle">
                {{-- Logo Munzalan dari public/images --}}
                <img src="{{ asset('storage/images/logo.png') }}" alt="Logo Munzalan">
            </div>
            <h4 class="fw-bold mb-1">Munzalan Inventory</h4>
            <p class="small opacity-75 mb-0">Silakan login menggunakan akun Anda.</p>
        </div>

        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger p-2 font-size-12 mb-3 rounded-3 small text-center border-0 bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success p-2 font-size-12 mb-3 rounded-3 small text-center border-0 bg-success bg-opacity-10 text-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                
                {{-- INPUT USERNAME --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Username</label>
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-transparent ps-0 text-secondary"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Masukan username" required value="{{ old('username') }}" autofocus>
                    </div>
                </div>

                {{-- INPUT PASSWORD --}}
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-transparent ps-0 text-secondary"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-login shadow">
                    Masuk Aplikasi <i class="fas fa-arrow-right ms-2 small"></i>
                </button>
            </form>
        </div>
        
        <div class="text-center pb-4 small text-muted">
            &copy; {{ date('Y') }} Sistem Aset Munzalan
        </div>
    </div>

</body>
</html>