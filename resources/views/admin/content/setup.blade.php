@extends('layouts.admin')

@section('title', 'Configurar Fuente')
@section('header_title', 'Agregar Fuente de Contenido')

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <a href="{{ route('admin.content') }}" class="btn" style="margin-bottom: 1.5rem; background: rgba(255,255,255,0.05); color: var(--text-dim);">
        <i data-lucide="arrow-left" size="18"></i>
        <span>Volver a Contenido</span>
    </a>

    <div class="card">
        <form action="{{ route('admin.content.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Nombre de la Fuente</label>
                <input type="text" name="name" class="form-input" placeholder="Ej: Mi Lista Premium" required>
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de Conexión</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <label style="border: 1px solid var(--border); padding: 1rem; border-radius: 15px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="type" value="xtream" checked onclick="toggleFields('xtream')" style="accent-color: var(--primary);">
                        <div>
                            <span style="display: block; font-weight: 600;">Xtream Codes</span>
                            <span style="font-size: 0.75rem; color: var(--text-dim);">Servidor + Usuario</span>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--border); padding: 1rem; border-radius: 15px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="type" value="m3u" onclick="toggleFields('m3u')" style="accent-color: var(--primary);">
                        <div>
                            <span style="display: block; font-weight: 600;">Lista M3U</span>
                            <span style="font-size: 0.75rem; color: var(--text-dim);">Enlace .m3u8</span>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--border); padding: 1rem; border-radius: 15px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="type" value="stremio" onclick="toggleFields('stremio')" style="accent-color: var(--primary);">
                        <div>
                            <span style="display: block; font-weight: 600;">Stremio</span>
                            <span style="font-size: 0.75rem; color: var(--text-dim);">Addon URL</span>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--border); padding: 1rem; border-radius: 15px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="type" value="mxl" onclick="toggleFields('mxl')" style="accent-color: var(--primary);">
                        <div>
                            <span style="display: block; font-weight: 600;">MXL / Otros</span>
                            <span style="font-size: 0.75rem; color: var(--text-dim);">Enlace Externo</span>
                        </div>
                    </label>
                    <label style="border: 1px solid var(--border); padding: 1rem; border-radius: 15px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                        <input type="radio" name="type" value="direct" onclick="toggleFields('direct')" style="accent-color: var(--primary);">
                        <div>
                            <span style="display: block; font-weight: 600;">Enlace Directo</span>
                            <span style="font-size: 0.75rem; color: var(--text-dim);">MP4 / MKV / HLS</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">URL del Servidor / Lista</label>
                <input type="url" name="url" class="form-input" placeholder="http://servidor.com:8080" required>
            </div>

            <div id="xtream-fields">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="username" class="form-input" placeholder="Tu usuario">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-input" placeholder="Tu contraseña">
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(255, 51, 51, 0.05); border-radius: 15px; border: 1px dashed var(--primary);">
                <p style="font-size: 0.85rem; color: var(--text-main); display: flex; gap: 10px; align-items: flex-start;">
                    <i data-lucide="info" size="18" style="color: var(--primary); flex-shrink: 0;"></i>
                    <span>Al guardar, el sistema intentará conectarse a la fuente. Luego deberás presionar "Sincronizar" para descargar el contenido.</span>
                </p>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i data-lucide="save" size="20"></i>
                    <span>Guardar Fuente de Contenido</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleFields(type) {
        const xtreamFields = document.getElementById('xtream-fields');
        if (type === 'xtream') {
            xtreamFields.style.display = 'block';
        } else {
            xtreamFields.style.display = 'none';
        }
    }
</script>
@endsection
