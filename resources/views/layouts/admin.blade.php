<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - ELECTROFABI IPTV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #ff3333;
            --primary-glow: rgba(255, 51, 51, 0.4);
            --bg-dark: #0a0a0c;
            --bg-card: #141417;
            --text-main: #e0e0e0;
            --text-dim: #9ca3af;
            --border: #2d2d33;
            --glass: rgba(20, 20, 23, 0.85);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(255, 51, 51, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(255, 51, 51, 0.05) 0%, transparent 50%);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        aside.sidebar {
            width: 280px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 3rem;
            padding: 0 0.5rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #fff, #999);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav.menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 1rem;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-dim);
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
            transform: translateX(4px);
        }

        .nav-item.active {
            background: rgba(255, 51, 51, 0.1);
            color: var(--primary);
            font-weight: 600;
        }

        .nav-item i {
            width: 20px;
            height: 20px;
        }

        /* Main Content */
        main.content {
            flex-grow: 1;
            margin-left: 280px;
            padding: 2rem 3rem;
        }

        header.top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .page-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 50px;
        }

        .avatar {
            width: 32px;
            height: 32px;
            background: #2d2d33;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Utils */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        /* Forms */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            outline: none;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 15px var(--primary-glow);
        }

        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 20px var(--primary-glow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dim);
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 1rem;
            background: #1a1a1e;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #fff;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            border-color: var(--primary);
        }

        @media (max-width: 1024px) {
            aside.sidebar {
                width: 80px;
                padding: 1.5rem 0.5rem;
            }
            .nav-item span, .logo-text {
                display: none;
            }
            main.content {
                margin-left: 80px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <div class="dashboard-container">
        @auth('admin')
        <aside class="sidebar">
            <div class="logo_area">
                <div class="logo-icon">
                    <i data-lucide="play" fill="#fff" stroke="none"></i>
                </div>
                <div class="logo-text">ELECTROFABI IPTV</div>
            </div>

            <nav class="menu">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i data-lucide="users"></i>
                    <span>Clientes</span>
                </a>
                <a href="{{ route('admin.content') }}" class="nav-item {{ request()->routeIs('admin.content*') ? 'active' : '' }}">
                    <i data-lucide="tv-2"></i>
                    <span>Contenido (IPTV)</span>
                </a>
                <a href="{{ route('admin.security') }}" class="nav-item {{ request()->routeIs('admin.security*') ? 'active' : '' }}">
                    <i data-lucide="shield-check"></i>
                    <span>Seguridad</span>
                </a>
                <a href="{{ route('admin.config') }}" class="nav-item {{ request()->routeIs('admin.config*') ? 'active' : '' }}">
                    <i data-lucide="settings"></i>
                    <span>Ajustes</span>
                </a>
                <a href="{{ route('admin.profile') }}" class="nav-item {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
                    <i data-lucide="user"></i>
                    <span>Mi Perfil</span>
                </a>

                <hr style="border-color: var(--border); margin: 10px 0; opacity: 0.5;">
                
                <a href="{{ route('admin.stremio.index') }}" class="nav-item {{ request()->routeIs('admin.stremio*') ? 'active' : '' }}">
                    <i data-lucide="puzzle"></i>
                    <span>Stremio Addons</span>
                </a>
                
                <form action="{{ route('admin.legal.sync') }}" method="POST" id="sync-form">
                    @csrf
                    <button type="submit" class="nav-item" style="background: none; border: none; width: 100%; cursor: pointer;" onclick="return confirm('¿Sincronizar canales legales ahora?')">
                        <i data-lucide="refresh-cw"></i>
                        <span>Sincronización Legal</span>
                    </button>
                </form>
            </nav>

            <form action="{{ route('admin.logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="nav-item" style="background: none; border: none; width: 100%; cursor: pointer;">
                    <i data-lucide="log-out"></i>
                    <span>Cerrar Sesión</span>
                </button>
            </form>
        </aside>
        @endauth

        <main class="content">
            @auth('admin')
            <header class="top-header">
                <div class="page-title">
                    <h1>@yield('header_title', 'Administración')</h1>
                </div>
                <a href="{{ route('admin.profile') }}" class="user-profile" style="text-decoration: none; color: inherit; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <div class="avatar">{{ substr(Auth::guard('admin')->user()->name, 0, 2) }}</div>
                    <span>{{ Auth::guard('admin')->user()->name }}</span>
                </a>
            </header>
            @endauth

            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>
