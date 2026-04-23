@extends('layouts.admin')

@section('title', 'Gestión de Addons de Stremio')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Formulario de Agregar -->
        <div class="col-md-4">
            <div class="card shadow-lg border-0 mb-4" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);">
                <div class="card-header bg-transparent border-0">
                    <h5 class="text-white mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Nuevo Addon</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.stremio.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="text-white-50 small mb-2">URL del Manifest (JSON)</label>
                            <input type="url" name="manifest_url" class="form-control bg-dark border-0 text-white" 
                                   placeholder="https://addon.stremio.com/manifest.json" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="fas fa-link me-2"></i>Conectar Addon
                        </button>
                    </form>
                    
                    <div class="mt-4 p-3 rounded bg-dark">
                        <p class="small text-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Los addons de Stremio te permiten agregar contenido de terceros como Torrentio, Dominio Público, etc.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Addons -->
        <div class="col-md-8">
            <div class="card shadow-lg border-0" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white mb-0">Mis Addons Conectados</h5>
                    <span class="badge bg-primary">{{ count($addons) }} Activos</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover border-0">
                            <thead>
                                <tr class="text-white-50 small text-uppercase">
                                    <th>Icono</th>
                                    <th>Nombre</th>
                                    <th>Tipos</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($addons as $addon)
                                <tr>
                                    <td>
                                        @if($addon->icon)
                                            <img src="{{ $addon->icon }}" width="40" class="rounded shadow-sm">
                                        @else
                                            <div class="rounded bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-puzzle-piece text-white-50"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white">{{ $addon->name }}</div>
                                        <div class="small text-white-50 truncate" style="max-width: 200px;">{{ $addon->manifest_url }}</div>
                                    </td>
                                    <td>
                                        @foreach($addon->catalog_types as $type)
                                            <span class="badge bg-dark border border-secondary text-capitalize">{{ $type }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.stremio.destroy', $addon) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm border-0" onclick="return confirm('¿Eliminar este addon?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-white-50">
                                        <i class="fas fa-plug fa-3x mb-3 d-block opacity-25"></i>
                                        Aún no has conectado ningún addon de Stremio.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endsection
