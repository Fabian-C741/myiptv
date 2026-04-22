@extends('layouts.admin')

@section('title', 'Gestión de Clientes')
@section('header_title', 'Administración de Usuarios')

@section('styles')
<style>
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        gap: 1rem;
    }

    .search-box {
        position: relative;
        flex-grow: 1;
        max-width: 400px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-dim);
    }

    .search-box input {
        padding-left: 2.5rem;
    }

    .user-table-card {
        padding: 0;
        overflow: hidden;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-main);
        transition: all 0.2s;
    }

    .btn-icon:hover {
        background: var(--primary);
        color: #fff;
    }

    .btn-icon.suspend:hover {
        background: #ef4444;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 2rem;
    }

    .pagination a, .pagination span {
        padding: 8px 16px;
        border-radius: 8px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        color: var(--text-main);
        text-decoration: none;
    }

    .pagination .active {
        background: var(--primary);
        border-color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="action-bar">
    <div class="search-box">
        <i data-lucide="search" size="18"></i>
        <form action="{{ route('admin.users') }}" method="GET">
            <input type="text" name="search" class="form-input" placeholder="Buscar por nombre o email..." value="{{ request('search') }}">
        </form>
    </div>
    
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i data-lucide="user-plus" size="18"></i>
        <span>Nuevo Cliente</span>
    </a>
</div>

<div class="card user-table-card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>CLIENTE</th>
                    <th>EMAIL</th>
                    <th>DISP. ACTIVOS</th>
                    <th>ESTADO</th>
                    <th>CREADO</th>
                    <th style="text-align: right;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar" style="background: rgba(255, 51, 51, 0.1); color: var(--primary);">{{ substr($user->name, 0, 1) }}</div>
                            <span style="font-weight: 600;">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td style="color: var(--text-dim);">{{ $user->email }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="smartphone" size="14" style="color: var(--primary)"></i>
                            {{ $user->devices_count }} / {{ $user->max_devices }}
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $user->status }}">
                            {{ $user->status == 'active' ? 'Activo' : 'Suspendido' }}
                        </span>
                    </td>
                    <td style="color: var(--text-dim); font-size: 0.85rem;">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn" style="padding: 6px 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                <i data-lucide="edit-3" size="16"></i>
                            </a>
                            <form action="{{ route('admin.users.status', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                @if($user->status === 'active')
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" class="btn" style="padding: 6px 12px; background: rgba(239, 68, 68, 0.1); color: #ef4444;" title="Suspender">
                                        <i data-lucide="user-minus" size="16"></i>
                                    </button>
                                @else
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn" style="padding: 6px 12px; background: rgba(52, 211, 153, 0.1); color: #34d399;" title="Activar">
                                        <i data-lucide="user-check" size="16"></i>
                                    </button>
                                @endif
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-dim);">
                        <i data-lucide="user-x" size="48" style="display: block; margin: 0 auto 1rem; opacity: 0.5;"></i>
                        No se encontraron clientes
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">
    {{ $users->links() }}
</div>
@endsection
