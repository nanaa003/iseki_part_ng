@extends('layouts.app')
@section('hide_navbar', true)

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--pink-600) 0%, var(--pink-800) 100%); position: relative; overflow: hidden;">
    <!-- Background decoration -->
    <div style="position: absolute; width: 300px; height: 300px; border-radius: 50%; background: rgba(255,255,255,0.05); top: -50px; right: -50px;"></div>
    <div style="position: absolute; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.05); bottom: -30px; left: -30px;"></div>

    <div class="fade-in" style="width: 100%; max-width: 420px; padding: 1.5rem;">
        <div class="text-center text-white mb-4">
            <div class="mb-3" style="width: 70px; height: 70px; border-radius: 20px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); display: inline-flex; align-items: center; justify-content: center;">
                <i class="bi bi-tools" style="font-size: 2rem;"></i>
            </div>
            <h3 class="fw-800">Admin Login</h3>
            <p class="opacity-75 small">Iseki Part NG - Sistem Monitor Permasalahan</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger py-2" style="border-radius: 12px; border: none;">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
        </div>
        @endif

        <div style="background: rgba(255,255,255,0.9); backdrop-filter: blur(20px); border-radius: 20px; padding: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">
                        <i class="bi bi-person me-1"></i>Username
                    </label>
                    <input type="text" name="Username_User" class="form-control form-control-lg" style="border-radius: 12px;" required autofocus placeholder="Masukkan username">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">
                        <i class="bi bi-lock me-1"></i>Password
                    </label>
                    <input type="password" name="Password_User" class="form-control form-control-lg" style="border-radius: 12px;" required placeholder="Masukkan password">
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
@endsection
