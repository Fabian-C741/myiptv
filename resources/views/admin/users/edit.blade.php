@extends('layouts.admin')

@section('title', 'Editar Cliente')
@section('header_title', 'Modificar Datos de Cliente')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <a href="{{ route('admin.users') }}" class="btn" style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.05); color: var(--text-dim);">
        <i data-lucide="arrow-left" size="18"></i>
        <span>Volver al listado</span>
    </a>

    <div class="card">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                @error('name') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                @error('email') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" class="form-input" placeholder="Dejar en blanco para no cambiar">
                <p style="font-size: 0.75rem; color: var(--text-dim); margin-top: 5px;">Solo completa este campo si deseas cambiar la contraseña del cliente.</p>
                @error('password') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Límite de Dispositivos Simultáneos</label>
                <select name="max_devices" class="form-input">
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ $user->max_devices == $i ? 'selected' : '' }}>{{ $i }} {{ $i == 1 ? 'Dispositivo' : 'Dispositivos' }}</option>
                    @endfor
                    <option value="20" {{ $user->max_devices == 20 ? 'selected' : '' }}>20 Dispositivos (Máximo)</option>
                </select>
                @error('max_devices') <span style="color: var(--primary); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i data-lucide="save" size="20"></i>
                    <span>Guardar Cambios del Cliente</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
