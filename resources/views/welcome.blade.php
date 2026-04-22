<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ElectroFab IPTV — Tu plataforma de streaming premium. Miles de canales en vivo, películas y series.">
    <meta name="robots" content="noindex, nofollow">
    <title>ElectroFab IPTV</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- Fuente moderna -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue:   #3b82f6;
            --purple: #8b5cf6;
            --cyan:   #06b6d4;
            --bg:     #04040a;
            --surface: rgba(255,255,255,0.04);
            --border:  rgba(255,255,255,0.08);
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: #fff;
            overflow: hidden;
        }

        /* ── Fondo animado ── */
        .bg-orbs {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.18;
            animation: drift 18s ease-in-out infinite;
        }

        .orb-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, #3b82f6, transparent 70%);
            top: -200px; left: -200px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #8b5cf6, transparent 70%);
            bottom: -150px; right: -150px;
            animation-delay: -6s;
        }

        .orb-3 {
            width: 350px; height: 350px;
            background: radial-gradient(circle, #06b6d4, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -12s;
        }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(40px, -30px) scale(1.05); }
            66%       { transform: translate(-30px, 40px) scale(0.95); }
        }

        /* Particles */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 2px; height: 2px;
            border-radius: 50%;
            background: rgba(99, 179, 237, 0.6);
            animation: float-up linear infinite;
        }

        @keyframes float-up {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* ── Centro ── */
        .center {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            gap: 2rem;
            padding: 2rem;
        }

        /* ── Logo card ── */
        .logo-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 3rem 4rem;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            box-shadow:
                0 0 0 1px rgba(59,130,246,0.15),
                0 25px 80px -10px rgba(0,0,0,0.8),
                inset 0 1px 0 rgba(255,255,255,0.07);
            animation: fade-in-up 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo-img {
            width: 260px;
            max-width: 80vw;
            height: auto;
            filter: drop-shadow(0 0 30px rgba(59,130,246,0.4));
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { filter: drop-shadow(0 0 20px rgba(59,130,246,0.3)); }
            50%       { filter: drop-shadow(0 0 45px rgba(139,92,246,0.5)); }
        }

        /* Divider */
        .divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,179,237,0.3), transparent);
        }

        /* Tagline */
        .tagline {
            font-size: 0.95rem;
            font-weight: 300;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
        }

        /* Status dot */
        .status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.3);
            letter-spacing: 0.1em;
        }

        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        /* Admin link (muy sutil) */
        .admin-hint {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 20;
        }

        .admin-hint a {
            color: rgba(255,255,255,0.12);
            text-decoration: none;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            transition: color 0.3s;
        }

        .admin-hint a:hover {
            color: rgba(255,255,255,0.4);
        }

        /* Copyright */
        .copyright {
            position: fixed;
            bottom: 1.5rem;
            left: 1.5rem;
            z-index: 20;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.1);
            letter-spacing: 0.05em;
        }

        @media (max-width: 480px) {
            .logo-card { padding: 2rem; }
            .logo-img  { width: 200px; }
        }
    </style>
</head>
<body>

    <!-- Fondos animados -->
    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Partículas flotantes -->
    <div class="particles" id="particles"></div>

    <!-- Contenido central -->
    <div class="center">
        <div class="logo-card">
            <img
                src="/logo.png"
                alt="ElectroFab IPTV"
                class="logo-img"
                onerror="this.style.display='none'; document.getElementById('text-logo').style.display='block';"
            >

            <!-- Fallback si no hay logo.png -->
            <div id="text-logo" style="display:none; text-align:center;">
                <div style="font-size:2.2rem; font-weight:900; background:linear-gradient(135deg,#3b82f6,#8b5cf6); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">ElectroFab</div>
                <div style="font-size:1.1rem; font-weight:600; color:#06b6d4; letter-spacing:0.3em;">IPTV</div>
            </div>

            <div class="divider"></div>

            <p class="tagline">Streaming Premium</p>

            <div class="status">
                <div class="status-dot"></div>
                <span>Servicio Activo</span>
            </div>
        </div>
    </div>

    <!-- Link admin discreto -->
    <div class="admin-hint">
        <a href="/admin/login">&#9679;</a>
    </div>

    <!-- Copyright -->
    <div class="copyright">
        &copy; {{ date('Y') }} ElectroFab IPTV
    </div>

    <script>
        // Generar partículas flotantes
        const container = document.getElementById('particles');
        const count = 35;
        for (let i = 0; i < count; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + 'vw';
            p.style.width = p.style.height = (Math.random() * 2 + 1) + 'px';
            p.style.animationDuration = (Math.random() * 15 + 10) + 's';
            p.style.animationDelay = -(Math.random() * 25) + 's';
            p.style.opacity = Math.random() * 0.5 + 0.1;
            container.appendChild(p);
        }
    </script>
</body>
</html>
