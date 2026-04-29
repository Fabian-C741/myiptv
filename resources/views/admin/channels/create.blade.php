@extends('layouts.admin')

@section('title', 'Nuevo Contenido')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-white">Agregar Contenido Personalizado</h1>
        <a href="{{ route('admin.channels.index') }}" class="btn btn-outline-light">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-dark text-white border-secondary">
                <div class="card-header border-secondary">
                    <h5 class="mb-0">Detalles del Contenido (Soporta YouTube)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.channels.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Nombre / Título</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Ej: Película Increíble">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="type" class="form-label">Tipo de Contenido</label>
                                <select class="form-select bg-dark text-white border-secondary @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="movie" {{ old('type') == 'movie' ? 'selected' : '' }}>Película (VOD)</option>
                                    <option value="live" {{ old('type') == 'live' ? 'selected' : '' }}>Transmisión en Vivo</option>
                                    <option value="series" {{ old('type') == 'series' ? 'selected' : '' }}>Serie</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="logo" class="form-label">URL del Póster (Vertical)</label>
                                <input type="url" class="form-control bg-dark text-white border-secondary @error('logo') is-invalid @enderror" id="logo" name="logo" value="{{ old('logo') }}" placeholder="https://ejemplo.com/poster.jpg">
                                @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="backdrop" class="form-label">URL del Fondo (Horizontal/Backdrop)</label>
                                <input type="url" class="form-control bg-dark text-white border-secondary @error('backdrop') is-invalid @enderror" id="backdrop" name="backdrop" value="{{ old('backdrop') }}" placeholder="https://ejemplo.com/fondo.jpg">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="release_date" class="form-label">Año de Estreno</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" id="release_date" name="release_date" value="{{ old('release_date') }}" placeholder="Ej: 2024">
                            </div>
                            <div class="col-md-4">
                                <label for="rating" class="form-label">Calificación (TMDB/Puntaje)</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" id="rating" name="rating" value="{{ old('rating') }}" placeholder="Ej: 8.5">
                            </div>
                            <div class="col-md-4">
                                <label for="duration" class="form-label">Duración / Calidad</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" id="duration" name="duration" value="{{ old('duration') }}" placeholder="Ej: 1h 45min / 4K">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="stream_url" class="form-label">URL del Video (YouTube o MP4/M3U8)</label>
                            <input type="url" class="form-control bg-dark text-white border-secondary @error('stream_url') is-invalid @enderror" id="stream_url" name="stream_url" value="{{ old('stream_url') }}" required placeholder="https://www.youtube.com/watch?v=XXXXXX">
                            <div class="form-text text-muted">Soporta enlaces directos y YouTube. El sistema extraerá el video automáticamente.</div>
                            @error('stream_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tmdb_id" class="form-label">ID de TMDB (Opcional)</label>
                                <input type="number" class="form-control bg-dark text-white border-secondary" id="tmdb_id" name="tmdb_id" value="{{ old('tmdb_id') }}" placeholder="Ej: 550">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_adult" name="is_adult" value="1" {{ old('is_adult') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_adult">Contenido Adulto (+18)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Sinopsis o Descripción</label>
                            <textarea class="form-control bg-dark text-white border-secondary" id="description" name="description" rows="3" placeholder="Resumen de la trama...">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-danger btn-lg px-5 shadow-sm">
                                <i class="fas fa-rocket me-2"></i> Publicar Ahora
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-dark text-white border-secondary">
                <div class="card-header border-secondary">
                    <h5 class="mb-0"><i class="fas fa-info-circle text-info"></i> Sobre YouTube</h5>
                </div>
                <div class="card-body">
                    <p>Puedes pegar cualquier enlace de video de YouTube directamente aquí.</p>
                    <p>La aplicación móvil se encargará de "desarmar" el enlace de YouTube por detrás y reproducir únicamente el archivo de video en pantalla completa con la interfaz estilo Netflix.</p>
                    <div class="alert alert-warning bg-transparent border-warning text-warning p-2">
                        <small>Nota: Las transmisiones en vivo (Live Streams) de YouTube también son soportadas, pero debes seleccionar el tipo "Transmisión en Vivo".</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
