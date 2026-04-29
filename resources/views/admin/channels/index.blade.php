@extends('layouts.admin')

@section('title', 'Gestor de Canales y VOD')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-white">Gestor de Contenido Sincronizado</h1>
        <div>
            <button id="bulk-check-btn" class="btn btn-outline-warning me-2">
                <i class="fas fa-broom"></i> 🚀 Auto-Limpiar Página
            </button>
            <a href="{{ route('admin.channels.create') }}" class="btn btn-danger">
                <i class="fas fa-plus"></i> Nuevo Contenido Personalizado (YouTube/Manual)
            </a>
        </div>
    </div>

    <div class="card bg-dark text-white border-secondary mb-4">
        <div class="card-header border-secondary d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.channels.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Buscar por nombre..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select bg-dark text-white border-secondary">
                        <option value="">Todos los Tipos</option>
                        <option value="live" {{ request('type') == 'live' ? 'selected' : '' }}>TV en Vivo</option>
                        <option value="movie" {{ request('type') == 'movie' ? 'selected' : '' }}>Película</option>
                        <option value="series" {{ request('type') == 'series' ? 'selected' : '' }}>Serie</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select bg-dark text-white border-secondary">
                        <option value="">Cualquier Estado</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Visibles (Activos)</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ocultos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-light w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-dark text-white border-secondary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="border-secondary">Logo</th>
                            <th scope="col" class="border-secondary">Nombre</th>
                            <th scope="col" class="border-secondary">Tipo</th>
                            <th scope="col" class="border-secondary text-center">Salud del Stream</th>
                            <th scope="col" class="border-secondary text-center">Visible en la App</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($channels as $channel)
                            <tr>
                                <td class="border-secondary">
                                    @if($channel->logo)
                                        <img src="{{ $channel->logo }}" alt="{{ $channel->name }}" style="height: 40px; border-radius: 4px; max-width: 80px; object-fit: contain;">
                                    @else
                                        <div class="bg-secondary text-center rounded d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-tv text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="border-secondary font-weight-bold">{{ $channel->name }}</td>
                                <td class="border-secondary">
                                    @if($channel->type == 'live')
                                        <span class="badge bg-danger">LIVE</span>
                                    @elseif($channel->type == 'movie')
                                        <span class="badge bg-primary">VOD</span>
                                    @else
                                        <span class="badge bg-success">SERIE</span>
                                    @endif
                                </td>
                                <td class="border-secondary text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="badge bg-secondary me-2 health-status-badge" id="status-{{ $channel->id }}">Desconocido</span>
                                        <button class="btn btn-sm btn-outline-info check-health-btn" data-id="{{ $channel->id }}" title="Probar conexión">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="border-secondary text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input toggle-status" type="checkbox" role="switch" data-id="{{ $channel->id }}" {{ $channel->is_active ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.2);">
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4 border-secondary">
                                    No se encontraron canales que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer border-secondary d-flex justify-content-center">
            {{ $channels->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Status
    const toggles = document.querySelectorAll('.toggle-status');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const channelId = this.getAttribute('data-id');
            const isActive = this.checked;

            fetch(`/admin/channels/${channelId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if(!data.success) {
                    alert('Error al actualizar el estado.');
                    this.checked = !isActive;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isActive;
            });
        });
    });

    // Health Check
    const checkBtns = document.querySelectorAll('.check-health-btn');
    checkBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const badge = document.getElementById(`status-${id}`);
            const icon = this.querySelector('i');

            // UI Feedback
            badge.innerText = 'Probando...';
            badge.className = 'badge bg-warning text-dark me-2 health-status-badge';
            icon.classList.add('fa-spin');
            this.disabled = true;

            fetch(`/admin/channels/${id}/check`)
            .then(response => response.json())
            .then(data => {
                icon.classList.remove('fa-spin');
                this.disabled = false;
                
                if (data.online) {
                    badge.innerText = 'ONLINE';
                    badge.className = 'badge bg-success me-2 health-status-badge';
                } else {
                    badge.innerText = 'CAÍDO';
                    badge.className = 'badge bg-danger me-2 health-status-badge';
                    // Si estaba activo y se ocultó, reflejarlo en el switch
                    const toggle = document.querySelector(`.toggle-status[data-id="${id}"]`);
                    if (toggle) toggle.checked = data.is_active;
                }
            })
            .catch(error => {
                icon.classList.remove('fa-spin');
                this.disabled = false;
                badge.innerText = 'ERROR';
                badge.className = 'badge bg-secondary me-2 health-status-badge';
            });
        });
    });

    // Bulk Check Logic
    const bulkBtn = document.getElementById('bulk-check-btn');
    bulkBtn.addEventListener('click', async function() {
        if (!confirm('¿Querés testear y ocultar automáticamente todos los canales caídos de esta página?')) return;
        
        this.disabled = true;
        const btns = Array.from(document.querySelectorAll('.check-health-btn'));
        
        for (const btn of btns) {
            btn.click(); // Ejecutar el clic individual
            // Esperar un poco entre cada uno para no saturar
            await new Promise(resolve => setTimeout(resolve, 1500)); 
        }
        
        this.disabled = false;
        alert('Limpieza de página completada.');
    });
});
</script>
@endsection
