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

    <div class="card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
            <i data-lucide="users" size="20" style="color: var(--primary);"></i>
            Perfiles Disponibles
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 1.5rem;">
            @forelse($user->profiles as $profile)
                <div style="text-align: center;">
                    <div style="width: 100px; height: 100px; margin: 0 auto 0.75rem; border-radius: 12px; overflow: hidden; background: #222; border: 2px solid rgba(255,255,255,0.05);">
                        @if($profile->avatar_url)
                            <img src="{{ asset('storage/' . $profile->avatar_url) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--primary); color: white; font-size: 2rem; font-weight: bold;">
                                {{ substr($profile->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div style="font-weight: 600; font-size: 0.9rem;">{{ $profile->name }}</div>
                    @if($profile->pin)
                        <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: 3px;">
                            <i data-lucide="lock" size="10" style="display: inline-block;"></i> PIN Activo
                        </div>
                    @endif
                </div>
            @empty
                <p style="grid-column: 1 / -1; text-align: center; color: var(--text-dim); padding: 1rem;">
                    Este usuario aún no ha creado perfiles.
                </p>
            @endforelse
        </div>
    </div>
</div>
@endsection
