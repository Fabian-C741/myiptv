@extends('layouts.admin')

@section('title', 'Editar Fuente IPTV')
@section('header_title', 'Modificar Origen de Contenido')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <a href="{{ route('admin.content') }}" class="btn" style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.05); color: var(--text-dim);">
        <i data-lucide="arrow-left" size="18"></i>
        <span>Volver a Contenido</span>
    </a>

    <div class="card">
        <form action="{{ route('admin.content.update', $playlist->id) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="form-group">
                <label class="form-label">Nombre de la Fuente</label>
                <input type="text" name="name" class="form-input" placeholder="Ej: Mi Lista Premium" value="{{ old('name', $playlist->name) }}" required>
                <p style="font-size: 0.75rem; color: var(--text-dim); margin-top: 5px;">Un nombre para identificar esta fuente internamente.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de Conexión</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <label class="connection-type {{ $playlist->type == 'xtream' ? 'active' : '' }}" id="label-xtream">
                        <input type="radio" name="type" value="xtream" style="display: none;" {{ $playlist->type == 'xtream' ? 'checked' : '' }}>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="radio-circle"></div>
                            <div>
                                <div style="font-weight: 600;">Xtream Codes</div>
                                <div style="font-size: 0.75rem; color: var(--text-dim);">Servidor + Usuario</div>
                            </div>
                        </div>
                    </label>
                    <label class="connection-type {{ $playlist->type == 'm3u' ? 'active' : '' }}" id="label-m3u">
                        <input type="radio" name="type" value="m3u" style="display: none;" {{ $playlist->type == 'm3u' ? 'checked' : '' }}>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="radio-circle"></div>
                            <div>
                                <div style="font-weight: 600;">Lista M3U</div>
                                <div style="font-size: 0.75rem; color: var(--text-dim);">Enlace .m3u8</div>
                            </div>
                        </div>
                    </label>
                    <label class="connection-type {{ $playlist->type == 'stremio' ? 'active' : '' }}" id="label-stremio">
                        <input type="radio" name="type" value="stremio" style="display: none;" {{ $playlist->type == 'stremio' ? 'checked' : '' }}>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="radio-circle"></div>
                            <div>
                                <div style="font-weight: 600;">Stremio</div>
                                <div style="font-size: 0.75rem; color: var(--text-dim);">Addon URL</div>
                            </div>
                        </div>
                    </label>
                    <label class="connection-type {{ $playlist->type == 'mxl' ? 'active' : '' }}" id="label-mxl">
                        <input type="radio" name="type" value="mxl" style="display: none;" {{ $playlist->type == 'mxl' ? 'checked' : '' }}>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="radio-circle"></div>
                            <div>
                                <div style="font-weight: 600;">MXL / Otros</div>
                                <div style="font-size: 0.75rem; color: var(--text-dim);">Enlace Externo</div>
                            </div>
                        </div>
                    </label>
                    <label class="connection-type {{ $playlist->type == 'direct' ? 'active' : '' }}" id="label-direct">
                        <input type="radio" name="type" value="direct" style="display: none;" {{ $playlist->type == 'direct' ? 'checked' : '' }}>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="radio-circle"></div>
                            <div>
                                <div style="font-weight: 600;">Enlace Directo</div>
                                <div style="font-size: 0.75rem; color: var(--text-dim);">MP4 / MKV / HLS</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">URL del Servidor / Lista</label>
                <input type="url" name="url" class="form-input" placeholder="http://servidor.com:8080" value="{{ old('url', $playlist->url) }}" required>
            </div>

            <div id="xtream-fields" style="display: {{ $playlist->type == 'xtream' ? 'grid' : 'none' }}; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-input" placeholder="Tu usuario" value="{{ old('username', $playlist->username) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-input" placeholder="Tu contraseña" value="{{ old('password', $playlist->password) }}">
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">
                    <i data-lucide="save" size="20"></i>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </form>

        <form action="{{ route('admin.content.destroy', $playlist->id) }}" method="POST" style="margin-top: 1rem;" onsubmit="return confirm('¿Estás seguro de eliminar esta fuente? Se borrarán todos los canales asociados.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn" style="width: 100%; justify-content: center; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">
                <i data-lucide="trash-2" size="20"></i>
                <span>Eliminar Fuente Permanentemente</span>
            </button>
        </form>
    </div>
</div>

<style>
    .connection-type {
        border: 2px solid rgba(255,255,255,0.05);
        padding: 1.5rem;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: rgba(255,255,255,0.02);
    }
    .connection-type.active {
        border-color: var(--primary);
        background: rgba(229, 9, 20, 0.05);
    }
    .radio-circle {
        width: 18px;
        height: 18px;
        border: 2px solid var(--text-dim);
        border-radius: 50%;
        position: relative;
    }
    .connection-type.active .radio-circle {
        border-color: var(--primary);
    }
    .connection-type.active .radio-circle::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 8px;
        height: 8px;
        background: var(--primary);
        border-radius: 50%;
    }
</style>

<script>
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const xtreamFields = document.getElementById('xtream-fields');
            document.querySelectorAll('.connection-type').forEach(label => label.classList.remove('active'));
            document.getElementById('label-' + e.target.value).classList.add('active');
            
            if (e.target.value === 'xtream') {
                xtreamFields.style.display = 'grid';
            } else {
                xtreamFields.style.display = 'none';
            }
        });
    });
</script>
@endsection
