@extends('layouts.admin')

@section('title', 'Mi Perfil')
@section('header_title', 'Configuración de Mi Cuenta')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div class="card">
        <form action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="form-group">
                <label class="form-label">Nombre del Administrador</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $admin->name) }}" required>
                @error('name') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico de Acceso</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $admin->email) }}" required>
                <p style="font-size: 0.75rem; color: var(--text-dim); margin-top: 5px;">Este es el correo que usas para entrar al panel.</p>
                @error('email') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 2rem 0;">

            <div class="form-group">
                <label class="form-label">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" class="form-input" placeholder="Dejar en blanco para no cambiar">
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Repite la contraseña">
                @error('password') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i data-lucide="shield-check" size="20"></i>
                    <span>Actualizar Mis Credenciales</span>
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(0,255,0,0.1); border: 1px solid rgba(0,255,0,0.2); border-radius: 8px; color: #4ade80; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="check-circle" size="20"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
</div>
@endsection
