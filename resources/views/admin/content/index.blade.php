@extends('layouts.admin')

@section('title', 'Gestión de Contenido')
@section('header_title', 'Fuentes de Contenido (IPTV)')

@section('styles')
<style>
    .source-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .source-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem;
        position: relative;
    }

    .type-badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-xtream { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .badge-m3u { background: rgba(168, 85, 247, 0.1); color: #a855f7; }

    .source-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin: 1.5rem 0;
    }

    .mini-stat {
        background: rgba(255, 255, 255, 0.03);
        padding: 12px;
        border-radius: 12px;
        text-align: center;
    }

    .mini-stat-val { font-weight: 700; font-size: 1.1rem; display: block; }
    .mini-stat-label { font-size: 0.75rem; color: var(--text-dim); }
</style>
@endsection

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div style="color: var(--text-dim);">Administra tus listas M3U y credenciales de Xtream Codes.</div>
    <a href="{{ route('admin.content.setup') }}" class="btn btn-primary">
        <i data-lucide="plus" size="18"></i>
        <span>Agregar Fuente</span>
    </a>
</div>

@if(session('success'))
    <div class="card" style="background: rgba(52, 211, 153, 0.1); color: #34d399; border-color: #34d399; margin-bottom: 2rem;">
        {{ session('success') }}
    </div>
@endif

<div class="source-grid">
    @forelse($playlists as $source)
    <div class="source-card">
        <span class="type-badge badge-{{ $source->type }}">{{ $source->type }}</span>
        <h3 style="margin-bottom: 5px;">{{ $source->name }}</h3>
        <p style="font-size: 0.85rem; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;">
            {{ $source->url }}
        </p>

        <div class="source-stats">
            <div class="mini-stat">
                <span class="mini-stat-val">{{ $source->channels_count }}</span>
                <span class="mini-stat-label">Canales totales</span>
            </div>
            <div class="mini-stat">
                <span class="mini-stat-val">{{ $source->channel_groups_count }}</span>
                <span class="mini-stat-label">Categorías</span>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <form action="{{ route('admin.content.sync', $source->id) }}" method="POST" style="flex-grow: 1;">
                @csrf
                <div style="display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn btn-primary sync-btn" style="flex: 1;">
                        <i data-lucide="refresh-cw" size="20"></i>
                        <span>Sincronizar ahora</span>
                    </button>
                    <a href="{{ route('admin.content.edit', $source->id) }}" class="btn" style="background: rgba(255, 255, 255, 0.05); color: var(--text-dim); padding: 12px">
                        <i data-lucide="settings" size="18"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
        <i data-lucide="tv-2" size="48" style="opacity: 0.3; margin-bottom: 1rem;"></i>
        <h3>No hay fuentes de contenido configuradas</h3>
        <p style="color: var(--text-dim); margin-bottom: 1.5rem;">Agrega una lista M3U o Xtream Codes para empezar a poblar tu App.</p>
        <a href="{{ route('admin.content.setup') }}" class="btn btn-primary">Configurar mi primera fuente</a>
    </div>
    @endforelse
</div>
@endsection
