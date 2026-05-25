<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'BiometricIP') | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" />
    <link id="app-style" href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @stack('styles')
    <style>
        /* ── Logo en sidebar ─────────────────────────────── */
        .sidenav-menu > a.logo {
            display: flex !important;
            align-items: center;
            justify-content: center;
            padding: 14px 16px;
            min-height: 70px;
            text-decoration: none;
        }
        /* Mostrar solo la variante light (modo por defecto) */
        .sidenav-menu > a.logo .logo-light { display: block !important; }
        .sidenav-menu > a.logo .logo-dark  { display: none  !important; }

        /* Dentro de logo-light: mostrar logo-lg, ocultar logo-sm */
        .sidenav-menu > a.logo .logo-light .logo-lg { display: block !important; }
        .sidenav-menu > a.logo .logo-light .logo-sm { display: none  !important; }

        /* Cuando el sidebar está contraído mostrar logo-sm */
        .sidenav-menu-condensed .sidenav-menu > a.logo .logo-light .logo-lg { display: none  !important; }
        .sidenav-menu-condensed .sidenav-menu > a.logo .logo-light .logo-sm { display: block !important; }

        /* Tamaño del logo — alta especificidad para ganarle al tema */
        html body .wrapper .sidenav-menu a.logo span img,
        html body .wrapper .sidenav-menu a.logo span span img {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: 56px !important;
            width: auto !important;
            max-width: 200px !important;
            object-fit: contain !important;
        }
        /* Logo-sm (sidebar contraído) */
        html body .wrapper .sidenav-menu a.logo .logo-sm img {
            height: 36px !important;
            max-width: 36px !important;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <header class="app-topbar">
            <div class="container-fluid topbar-menu">
                <div class="d-flex align-items-center gap-2">
                    <div class="logo-topbar">
                        <a href="{{ url('/') }}" class="logo-light">
                            <img src="{{ asset('logos/logo_menu.png') }}" alt="Logo" style="max-height:36px; object-fit:contain;">
                        </a>
                        <a href="{{ url('/') }}" class="logo-dark">
                            <img src="{{ asset('logos/logo_menu.png') }}" alt="Logo" style="max-height:36px; object-fit:contain;">
                        </a>
                    </div>
                    <button class="sidenav-toggle-button btn btn-primary btn-icon">
                        <i class="ti ti-menu-4"></i>
                    </button>
                </div>
                <ul class="list-unstyled topbar-nav float-end mb-0">
                    <li class="nav-item dropdown pe-3">
                        <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="account-user-avatar">
                                <span class="avatar-title bg-primary rounded-circle fw-bold">{{ substr(auth()->user()->name ?? 'A', 0, 2) }}</span>
                            </span>
                            <span class="d-lg-block d-none">
                                <span class="account-user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <span class="account-position fw-semibold text-muted fs-12 d-block">{{ auth()->user()->role ?? 'Administrador' }}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item text-danger fw-semibold" onclick="document.getElementById('frm-logout').submit()">
                                <i class="ti ti-logout me-1 fs-lg align-middle"></i> Cerrar Sesión
                            </a>
                            <form id="frm-logout" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </header>

        <div class="sidenav-menu">
            <a href="{{ url('/') }}" class="logo">
                <span class="logo logo-light">
                    <span class="logo-lg"><img src="{{ asset('logos/logo.png') }}" alt="Logo"></span>
                    <span class="logo-sm"><img src="{{ asset('logos/logo_menu.png') }}" alt="Logo"></span>
                </span>
                <span class="logo logo-dark">
                    <span class="logo-lg"><img src="{{ asset('logos/logo.png') }}" alt="Logo"></span>
                    <span class="logo-sm"><img src="{{ asset('logos/logo_menu.png') }}" alt="Logo"></span>
                </span>
            </a>
            <button class="button-on-hover"><span class="btn-on-hover-icon"></span></button>
            <button class="button-close-offcanvas"><i class="ti ti-menu-4 align-middle"></i></button>

            <div class="scrollbar" data-simplebar="">
                <div id="user-profile-settings" class="sidenav-user" style="background: url({{ asset('assets/images/user-bg-pattern.svg') }})">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="sidenav-user-name fw-bold">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <span class="fs-12 fw-semibold d-block">{{ auth()->user()->role === 'admin' ? 'Administrador' : 'Empleado' }}</span>
                        </div>
                    </div>
                </div>

                <div id="sidenav-menu">
                    <ul class="side-nav">
                        <li class="side-nav-title mt-2">Menú Principal</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </li>

                        <li class="side-nav-title mt-2">Administración</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sedes.index') }}" class="side-nav-link {{ request()->routeIs('admin.sedes.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-building"></i></span>
                                <span class="menu-text">Sedes</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.empleados.index') }}" class="side-nav-link {{ request()->routeIs('admin.empleados.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-users"></i></span>
                                <span class="menu-text">Empleados</span>
                            </a>
                        </li>

                        <li class="side-nav-title mt-2">Asistencia</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.attendance.index') }}" class="side-nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-clock"></i></span>
                                <span class="menu-text">Registros</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.resumen.index') }}" class="side-nav-link {{ request()->routeIs('admin.resumen.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-calendar-stats"></i></span>
                                <span class="menu-text">Resumen Marcación</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.permisos.index') }}" class="side-nav-link {{ request()->routeIs('admin.permisos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-file-certificate"></i></span>
                                <span class="menu-text">Permisos</span>
                            </a>
                        </li>

                        <li class="side-nav-title mt-2">Organización</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.departamentos.index') }}" class="side-nav-link {{ request()->routeIs('admin.departamentos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-sitemap"></i></span>
                                <span class="menu-text">Deptos. y Cargos</span>
                            </a>
                        </li>

                        <li class="side-nav-title mt-2">Empresa</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.empresas.index') }}" class="side-nav-link {{ request()->routeIs('admin.empresas.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-building-skyscraper"></i></span>
                                <span class="menu-text">Mi Empresa</span>
                            </a>
                        </li>

                        <li class="side-nav-title mt-2">Configuración</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.horarios.index') }}" class="side-nav-link {{ request()->routeIs('admin.horarios.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-clock-play"></i></span>
                                <span class="menu-text">Horarios</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.festivos.index') }}" class="side-nav-link {{ request()->routeIs('admin.festivos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-calendar-x"></i></span>
                                <span class="menu-text">Festivos</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="content-page">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Interceptar fetch de traducciones para evitar 404
        const _fetch = window.fetch;
        window.fetch = function(url, ...args) {
            if (typeof url === 'string' && url.includes('/translations/')) {
                return Promise.resolve(new Response('{}', {
                    status: 200,
                    headers: { 'Content-Type': 'application/json' }
                }));
            }
            return _fetch(url, ...args);
        };
    </script>
    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        @if(session('api_token'))
        localStorage.setItem('token', '{{ session('api_token') }}');
        @endif
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    @stack('scripts')
</body>
</html>
