@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Vista General')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        padding: 1.8rem;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border);
        background: var(--bg-card);
        border-radius: 20px;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: var(--primary);
        filter: blur(80px);
        opacity: 0.1;
        pointer-events: none;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: var(--primary);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .stat-label {
        color: var(--text-dim);
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        padding: 1rem;
        color: var(--text-dim);
        font-weight: 500;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border);
    }

    td {
        padding: 1.2rem 1rem;
        border-bottom: 1px solid var(--border);
    }

    .status-badge {
        display: inline-flex;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active { background: rgba(52, 211, 153, 0.1); color: #34d399; }
    .status-suspended { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    .chart-container {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #1a1a1e;
        border-radius: 15px;
        color: var(--text-dim);
        font-style: italic;
    }
</style>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i data-lucide="users" size="24"></i></div>
        <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
        <div class="stat-label">Usuarios Totales</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="color: #34d399;"><i data-lucide="activity" size="24"></i></div>
        <div class="stat-value">{{ number_format($stats['active_users']) }}</div>
        <div class="stat-label">Clientes Activos</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="color: #60a5fa;"><i data-lucide="monitor" size="24"></i></div>
        <div class="stat-value">{{ number_format($stats['active_sessions']) }}</div>
        <div class="stat-label">Sesiones en Vivo</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="color: #f59e0b;"><i data-lucide="tv-2" size="24"></i></div>
        <div class="stat-value">{{ number_format($stats['total_channels']) }}</div>
        <div class="stat-label">Canales Online</div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="section-header">
            <h3>Conexiones Recientes</h3>
            <a href="{{ route('admin.users') }}" class="btn" style="padding: 8px 16px; font-size: 0.8rem; background: #2d2d33;">Ver todos</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>USUARIO</th>
                        <th>IP / PAÍS</th>
                        <th>DISPOSITIVO</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sampleUsers = \App\Models\User::latest()->take(5)->get();
                    @endphp
                    @forelse($sampleUsers as $user)
                    <tr>
                        <td style="font-weight: 500;">{{ $user->name }}</td>
                        <td><span style="color: var(--text-dim);">Desconocida</span></td>
                        <td><i data-lucide="smartphone" size="14" style="vertical-align: middle;"></i> Smart TV</td>
                        <td><span class="status-badge status-{{ $user->status }}">{{ $user->status == 'active' ? 'Conectado' : 'Fuera' }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 2rem;">No hay usuarios registrados aún</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="section-header">
            <h3>Distribución</h3>
        </div>
        <div class="chart-container">
            <p>Módulo de gráficas (Analytics) próximamente...</p>
        </div>
        <div style="margin-top: 1.5rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Uso de Red</span>
                <span>45%</span>
            </div>
            <div style="width: 100%; height: 6px; background: #2d2d33; border-radius: 10px;">
                <div style="width: 45%; height: 100%; background: var(--primary); border-radius: 10px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection
