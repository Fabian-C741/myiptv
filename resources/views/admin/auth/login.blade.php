@extends('layouts.admin')

@section('title', 'Iniciar Sesión')

@section('styles')
<style>
    body {
        background-image: 
            radial-gradient(circle at 50% 50%, rgba(255, 51, 51, 0.1) 0%, transparent 70%),
            url('https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?q=80&w=2069&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    main.content {
        margin-left: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 0;
    }

    .login-box {
        width: 100%;
        max-width: 450px;
        background: var(--glass);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        padding: 3rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .login-logo {
        width: 60px;
        height: 60px;
        background: var(--primary);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: 0 0 30px var(--primary-glow);
    }

    .login-header h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #fff;
    }

    .login-header p {
        color: var(--text-dim);
    }

    .login-button {
        width: 100%;
        justify-content: center;
        margin-top: 1rem;
        padding: 14px;
        font-size: 1.1rem;
    }

    .error-msg {
        background: rgba(255, 51, 51, 0.1);
        border: 1px solid var(--primary);
        color: var(--primary);
        padding: 12px;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        display: none;
    }
    
    @if ($errors->any())
    .error-msg {
        display: block;
    }
    @endif
</style>
@endsection

@section('content')
<div class="login-box">
    <div class="login-header">
        <div class="login-logo">
            <i data-lucide="play" fill="#fff" stroke="none" size="32"></i>
        </div>
        <h2>Panel de Control</h2>
        <p>Inicia sesión para gestionar tu plataforma OTT</p>
    </div>

    @if ($errors->any())
    <div class="error-msg">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
    @endif

    <form action="{{ route('admin.login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Correo Electrónico</label>
            <input type="email" name="email" class="form-input" placeholder="admin@apktv.com" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" name="remember" id="remember" style="accent-color: var(--primary);">
            <label for="remember" class="form-label" style="margin-bottom: 0; cursor: pointer;">Recordar sesión</label>
        </div>

        <button type="submit" class="btn btn-primary login-button">
            <span>Ingresar al Panel</span>
            <i data-lucide="chevron-right" size="20"></i>
        </button>
    </form>
</div>
@endsection
