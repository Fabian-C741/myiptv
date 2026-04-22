@extends('layouts.admin')

@section('title', 'Seguridad')
@section('header_title', 'Seguridad y Auditoría')

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
    <!-- Alertas Recientes -->
    <div class="card" style="grid-column: span 2;">
        <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="bell" style="color: var(--primary);"></i>
            Alertas de Seguridad Recientes
        </h3>
        @if($alerts->isEmpty())
            <p style="color: var(--text-dim); text-align: center; padding: 2rem;">No hay alertas pendientes.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                            <th style="padding: 1rem 0; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Tipo</th>
                            <th style="padding: 1rem 0; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Usuario</th>
                            <th style="padding: 1rem 0; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Severidad</th>
                            <th style="padding: 1rem 0; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Fecha</th>
                            <th style="padding: 1rem 0; color: var(--text-dim); font-size: 0.8rem; text-transform: uppercase;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                            <td style="padding: 1.2rem 0;">
                                <div style="font-weight: 600;">{{ $alert->type }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-dim);">IP: {{ $alert->ip_address }}</div>
                            </td>
                            <td style="padding: 1.2rem 0;">{{ $alert->user->name ?? 'N/A' }}</td>
                            <td style="padding: 1.2rem 0;">
                                <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; background: {{ $alert->severity == 'high' ? 'rgba(255,51,51,0.1)' : 'rgba(255,255,255,0.05)' }}; color: {{ $alert->severity == 'high' ? '#ff3333' : '#fff' }};">
                                    {{ strtoupper($alert->severity) }}
                                </span>
                            </td>
                            <td style="padding: 1.2rem 0; color: var(--text-dim); font-size: 0.85rem;">{{ $alert->created_at->diffForHumans() }}</td>
                            <td style="padding: 1.2rem 0;">
                                @if(!$alert->resolved)
                                <form action="{{ route('admin.security.resolve', $alert->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: rgba(255,255,255,0.1);">Resolver</button>
                                </form>
                                @else
                                <span style="color: #10b981; font-size: 0.8rem;">✓ Resuelto</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- IPs con Multi-Cuenta -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="crosshair" style="color: #fbbf24;"></i>
            IPs con Múltiples Usuarios
        </h3>
        <p style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 1.5rem;">Clientes distintos compartiendo la misma conexión.</p>
        
        @if($suspiciousIps->isEmpty())
            <p style="color: var(--text-dim); text-align: center;">No se detectaron IPs sospechosas.</p>
        @else
            @foreach($suspiciousIps as $ips)
            <div style="background: rgba(255,255,255,0.02); border-radius: 12px; margin-bottom: 1rem; border: 1px solid var(--border); overflow: hidden;">
                <div style="padding: 12px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.03);">
                    <span style="font-family: monospace; font-weight: 700;">{{ $ips->ip_address }}</span>
                    <span style="background: #ff3333; color: #fff; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700;">{{ $ips->user_count }} USUARIOS</span>
                </div>
                <div style="padding: 10px;">
                    @foreach($ips->users as $u)
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.85rem;">
                        <span style="color: {{ $u->status == 'suspended' ? 'var(--text-dim)' : '#fff' }};">
                            {{ $u->name }} 
                            @if($u->status == 'suspended') <small>(Suspendido)</small> @endif
                        </span>
                        @if($u->status != 'suspended')
                        <form action="{{ route('admin.users.status', $u->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="suspended">
                            <button type="submit" style="background: none; border: none; color: #ff3333; cursor: pointer; font-size: 0.75rem; text-decoration: underline;">Suspender</button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Usuarios en múltiples países -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="globe" style="color: #3b82f6;"></i>
            Conexiones Multi-País
        </h3>
        @if($multiCountry->isEmpty())
            <p style="color: var(--text-dim); text-align: center;">No hay usuarios reportando conexiones externas múltiples.</p>
        @else
            @foreach($multiCountry as $m)
            <div style="margin-bottom: 1.2rem; padding: 12px; background: rgba(59,130,246,0.05); border-radius: 12px; border: 1px solid rgba(59,130,246,0.2);">
                <div style="font-weight: 600;">{{ $m->user->name }}</div>
                <div style="font-size: 0.8rem; color: var(--text-dim);">{{ $m->user->email }}</div>
                <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: #ff3333; font-weight: 700; font-size: 0.9rem;">{{ $m->country_count }} Países Detected</span>
                    <form action="{{ route('admin.users.status', $m->user_id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="suspended">
                        <button type="submit" class="btn" style="background: #ff3333; color: #fff; padding: 4px 10px; font-size: 0.75rem; border-radius: 6px;">Suspender Cuenta</button>
                    </form>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
