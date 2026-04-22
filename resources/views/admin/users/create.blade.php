@extends('layouts.admin')

@section('title', 'Nuevo Cliente')
@section('header_title', 'Registrar Nuevo Cliente')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <a href="{{ route('admin.users') }}" class="btn" style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.05); color: var(--text-dim);">
        <i data-lucide="arrow-left" size="18"></i>
        <span>Volver al listado</span>
    </a>

    <div class="card">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="name" class="form-input" placeholder="Ej: Juan Pérez" value="{{ old('name') }}" required>
                @error('name') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" class="form-input" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>
                @error('email') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña de Acceso</label>
                <input type="password" name="password" class="form-input" placeholder="Mínimo 6 caracteres" required>
                <p style="font-size: 0.8rem; color: var(--text-dim); margin-top: 5px;">Esta será la contraseña que el cliente usará en la App de TV.</p>
                @error('password') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Límite de Dispositivos Simultáneos</label>
                <select name="max_devices" class="form-input">
                    <option value="1">1 Dispositivo</option>
                    <option value="2">2 Dispositivos</option>
                    <option value="3" selected>3 Dispositivos (Recomendado)</option>
                    <option value="5">5 Dispositivos</option>
                    <option value="10">10 Dispositivos (Plan Premium)</option>
                </select>
                @error('max_devices') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i data-lucide="user-plus" size="20"></i>
                    <span>Crear Cliente Ahora</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
