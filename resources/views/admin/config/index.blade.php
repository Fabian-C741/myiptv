@extends('layouts.admin')

@section('title', 'Ajustes de Marca')
@section('header_title', 'Configuración de ELECTROFABI IPTV')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    @if(session('success'))
        <div class="card" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border-color: #34d399; margin-bottom: 2rem;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="card" style="background: rgba(255, 51, 51, 0.1); color: #ff3333; border-color: #ff3333; margin-bottom: 2rem;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="overflow: hidden;">
        <div style="padding: 2rem; border-bottom: 1px solid var(--border); background: linear-gradient(to right, rgba(0, 170, 255, 0.05), rgba(255, 51, 51, 0.05));">
            <h3 style="margin-bottom: 10px;">Identidad de la Plataforma</h3>
            <p style="color: var(--text-dim); font-size: 0.9rem;">Cambia el nombre, el logo y la versión que verán tus clientes en sus dispositivos.</p>
        </div>

        <form action="{{ route('admin.config.update') }}" method="POST" enctype="multipart/form-data" style="padding: 2rem;">
            @csrf
            @method('PATCH')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Nombre Comercial</label>
                        <input type="text" name="app_name" class="form-input" value="{{ $settings['app_name'] }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Versión del APK (Actual)</label>
                        <input type="text" name="app_version" class="form-input" value="{{ $settings['app_version'] }}" placeholder="1.0.0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">URL de descarga del APK</label>
                        <input type="url" name="app_apk_url" class="form-input" value="{{ $settings['app_apk_url'] }}" placeholder="https://tu-dominio.com/electrofab.apk">
                    </div>

                    <div class="form-group" style="margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 10px; border: 1px dashed var(--border);">
                        <label class="form-label" style="color: #00aaff;">🚀 Subir Nuevo APK (Automático)</label>
                        <input type="file" name="apk_file" class="form-input" accept=".apk">
                        <p style="font-size: 0.75rem; color: var(--text-dim); margin-top: 5px;">
                            ✅ Al subir el APK, el sistema detecta la versión automáticamente del archivo compilado y actualiza la URL. No hace falta tocar nada más.
                        </p>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label class="form-label">Logo de la App</label>
                        <div style="width: 100%; height: 150px; background: #1a1a1e; border: 1px dashed var(--border); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; overflow: hidden;">
                            @if($settings['app_logo'])
                                <img src="{{ \Storage::url($settings['app_logo']) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            @else
                                <div style="text-align: center; color: var(--text-dim);">
                                    <i data-lucide="image" size="32" style="display: block; margin: 0 auto 8px;"></i>
                                    <span style="font-size: 0.8rem;">Sin logo (Se usa el de por defecto)</span>
                                </div>
                            @endif
                        </div>
                        <input type="file" name="logo_file" class="form-input" accept="image/*">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;">
                <div class="form-group">
                    <label class="form-label">Color Primario</label>
                    <input type="color" name="primary_color" value="{{ $settings['primary_color'] }}" style="width: 100%; height: 45px; cursor: pointer; background: none; border: none;">
                </div>
                <div class="form-group">
                    <label class="form-label">Contacto Soporte (WhatsApp)</label>
                    <input type="text" name="whatsapp_contact" class="form-input" value="{{ $settings['whatsapp_contact'] }}" placeholder="+5491100000000">
                    <p style="font-size: 0.75rem; color: var(--text-dim); margin-top: 5px;">Incluye el código de país (ej: +54 para Argentina).</p>
                </div>
            </div>

            <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(0, 170, 255, 0.05); border-radius: 15px; border: 1px dashed #00aaff;">
                <p style="font-size: 0.85rem; color: var(--text-main); display: flex; gap: 10px; align-items: flex-start;">
                    <i data-lucide="shield-check" size="18" style="color: #00aaff; flex-shrink: 0;"></i>
                    <span>Toda la comunicación entre el Panel y el APK está cifrada y protegida. Estos cambios se reflejarán cuando el cliente reinicie su sesión.</span>
                </p>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #00aaff; box-shadow: 0 4px 15px rgba(0, 170, 255, 0.4);">
                    <i data-lucide="save" size="20"></i>
                    <span>Guardar Cambios de Marca</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
