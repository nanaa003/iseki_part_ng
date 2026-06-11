<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Iseki Part NG</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/inter.css') }}" rel="stylesheet">
    <style>
        :root{--pink-500:#ff1493;--pink-600:#db0a70;--pink-700:#b30054;--pink-800:#8a003f;--pink-900:#61002b}
        *{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
        .fade-in{animation:fadeIn .5s ease-out}
        @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .btn-pink{background:linear-gradient(135deg,var(--pink-500),var(--pink-600));border:none;color:#fff;font-weight:600;border-radius:12px;padding:.75rem 1.5rem;transition:all .3s;box-shadow:0 4px 14px rgba(236,72,153,.3)}
        .btn-pink:hover{background:linear-gradient(135deg,var(--pink-600),var(--pink-700));box-shadow:0 6px 20px rgba(236,72,153,.4);transform:translateY(-1px);color:#fff}
        .login-shape{position:absolute;border-radius:50%;pointer-events:none}
    </style>
</head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--pink-700) 0%,var(--pink-800) 50%,var(--pink-900) 100%);position:relative;overflow:hidden">
    <div class="login-shape" style="width:300px;height:300px;background:rgba(255,255,255,.05);top:-50px;right:-50px"></div>
    <div class="login-shape" style="width:200px;height:200px;background:rgba(255,255,255,.05);bottom:-30px;left:-30px"></div>
    <div class="login-shape" style="width:150px;height:150px;background:rgba(255,255,255,.03);top:40%;left:10%"></div>
    <div class="login-shape" style="width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,.03),transparent);bottom:-200px;right:-100px"></div>

    <div class="fade-in" style="width:100%;max-width:420px;padding:1.5rem">
        <div class="text-center text-white mb-4">
            <div class="mb-3" style="width:70px;height:70px;border-radius:20px;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);display:inline-flex;align-items:center;justify-content:center">
                <i class="bi bi-tools" style="font-size:2rem"></i>
            </div>
            <h3 class="fw-800">Login</h3>
            <p class="opacity-75 small">Iseki Part NG — Sistem Monitor Permasalahan</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger py-2" style="border-radius:12px;border:none">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
        </div>
        @endif

        <div style="background:rgba(255,255,255,.9);backdrop-filter:blur(20px);border-radius:20px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.15)">
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">
                        <i class="bi bi-person me-1"></i>Username
                    </label>
                    <input type="text" name="Username_User" class="form-control form-control-lg" style="border-radius:12px" required autofocus placeholder="Masukkan username">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">
                        <i class="bi bi-lock me-1"></i>Password
                    </label>
                    <input type="password" name="Password_User" class="form-control form-control-lg" style="border-radius:12px" required placeholder="Masukkan password">
                </div>
                <button type="submit" class="btn btn-pink btn-lg w-100 py-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="{{ url('/') }}" class="text-white opacity-75 text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Halaman Utama
            </a>
        </div>
    </div>
</div>
</body>
</html>
