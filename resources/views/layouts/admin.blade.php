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
        html[data-sidenav-size=condensed] .sidenav-menu > a.logo .logo-light .logo-lg { display: none  !important; }
        html[data-sidenav-size=condensed] .sidenav-menu > a.logo .logo-light .logo-sm { display: block !important; }

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
                <ul class="list-unstyled topbar-nav float-end mb-0 d-flex align-items-center">

                    <li class="topbar-item">
                        <a class="topbar-link" href="javascript:void(0)" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" title="Personalizar">
                            <i class="ti ti-settings topbar-link-icon"></i>
                        </a>
                    </li>
                    <div class="topbar-item d-none d-sm-flex">
                        <button class="topbar-link" id="light-dark-mode" type="button">
                            <i class="ti ti-moon topbar-link-icon"></i>
                        </button>
                    </div>
                    <li class="nav-item dropdown pe-3">
                        <a class="nav-link dropdown-toggle arrow-none nav-user px-3" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <span class="account-user-avatar">
                                <span class="avatar-title bg-primary rounded-circle fw-bold">{{ substr(auth()->user()->name ?? 'A', 0, 2) }}</span>
                            </span>
                            <span class="d-lg-block d-none">
                                <span class="account-user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <span class="account-position fw-semibold text-muted fs-12 d-block">{{ auth()->user()->role ?? 'Administrador' }}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="{{ url('/') }}" class="dropdown-item fw-semibold">
                                <i class="ti ti-home me-1 fs-lg align-middle"></i> Ir al Inicio
                            </a>
                            <div class="dropdown-divider"></div>
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
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <span class="logo logo-light">
                    <span class="logo-lg"><img src="{{ asset('logos/logo_blanco.png') }}" alt="Logo"></span>
                    <span class="logo-sm"><img src="{{ asset('logos/logo_menu.png') }}" alt="Logo"></span>
                </span>
                <span class="logo logo-dark">
                    <span class="logo-lg"><img src="{{ asset('logos/logo_blanco.png') }}" alt="Logo"></span>
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
                        <li class="side-nav-item">
                            <a href="{{ route('admin.visitantes.index') }}" class="side-nav-link {{ request()->routeIs('admin.visitantes.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-user-check"></i></span>
                                <span class="menu-text">Visitantes</span>
                            </a>
                        </li>

                        <li class="side-nav-item">
                            <a href="{{ route('admin.dispositivos.index') }}" class="side-nav-link {{ request()->routeIs('admin.dispositivos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-fingerprint"></i></span>
                                <span class="menu-text">Dispositivos</span>
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

    {{-- ===== ADMIN CUSTOMIZER ===== --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="theme-settings-offcanvas" style="width:300px">
        <div class="offcanvas-header" style="background:linear-gradient(135deg,#1abc9c,#16a085);padding:20px 16px;">
            <div>
                <h5 class="offcanvas-title text-white fw-bold mb-0" style="letter-spacing:.5px">ADMIN CUSTOMIZER</h5>
                <small class="text-white opacity-75">Configure el layout y estilo de la interfaz.</small>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-0" style="overflow-y:auto">

            {{-- Color Scheme --}}
            <div class="p-3 border-bottom">
                <p class="fw-semibold mb-3">Color Scheme</p>
                <div class="d-flex gap-3">
                    @foreach([['light','Light'],['dark','Dark'],['system','System']] as [$val,$label])
                    <label class="flex-fill text-center" style="cursor:pointer">
                        <input type="radio" name="data-bs-theme" value="{{ $val }}" class="d-none">
                        <div class="theme-thumb border rounded-2 mb-1 position-relative overflow-hidden" style="height:60px;background:{{ $val==='dark'?'#222':'#f0f0f0' }}">
                            @if($val==='system')
                                <div style="position:absolute;left:0;top:0;width:50%;height:100%;background:#222"></div>
                                <div style="position:absolute;right:0;top:0;width:50%;height:100%;background:#f0f0f0"></div>
                            @endif
                            <div style="position:absolute;left:8px;top:8px;width:14px;bottom:8px;background:{{ $val==='dark'?'#444':'#ccc' }};border-radius:2px"></div>
                            <div style="position:absolute;left:26px;top:8px;right:8px;height:8px;background:{{ $val==='dark'?'#555':'#ddd' }};border-radius:2px"></div>
                            <div style="position:absolute;left:26px;top:20px;right:8px;height:5px;background:{{ $val==='dark'?'#444':'#e5e5e5' }};border-radius:2px"></div>
                            <div style="position:absolute;left:26px;top:29px;right:8px;height:5px;background:{{ $val==='dark'?'#444':'#e5e5e5' }};border-radius:2px"></div>
                            <span class="check-icon position-absolute" style="display:none;bottom:4px;right:4px;width:18px;height:18px;background:#1abc9c;border-radius:50%;align-items:center;justify-content:center">
                                <i class="ti ti-check text-white" style="font-size:11px"></i>
                            </span>
                        </div>
                        <small class="text-muted">{{ $label }}</small>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Sidebar Size --}}
            <div class="p-3 border-bottom">
                <p class="fw-semibold mb-3">Sidebar Size</p>
                <div class="d-flex gap-3">
                    @foreach([['default','Default'],['compact','Compact'],['condensed','Condensed']] as [$val,$label])
                    <label class="flex-fill text-center" style="cursor:pointer">
                        <input type="radio" name="data-sidenav-size" value="{{ $val }}" class="d-none">
                        <div class="theme-thumb border rounded-2 mb-1 position-relative overflow-hidden" style="height:60px;background:#f0f0f0">
                            <div style="position:absolute;left:8px;top:8px;width:{{ $val==='condensed'?'8px':($val==='compact'?'10px':'14px') }};bottom:8px;background:#ccc;border-radius:2px"></div>
                            <div style="position:absolute;left:{{ $val==='condensed'?'20px':($val==='compact'?'22px':'26px') }};top:8px;right:8px;height:8px;background:#ddd;border-radius:2px"></div>
                            <div style="position:absolute;left:{{ $val==='condensed'?'20px':($val==='compact'?'22px':'26px') }};top:20px;right:8px;height:5px;background:#e5e5e5;border-radius:2px"></div>
                            <div style="position:absolute;left:{{ $val==='condensed'?'20px':($val==='compact'?'22px':'26px') }};top:29px;right:8px;height:5px;background:#e5e5e5;border-radius:2px"></div>
                            <span class="check-icon position-absolute" style="display:none;bottom:4px;right:4px;width:18px;height:18px;background:#1abc9c;border-radius:50%;align-items:center;justify-content:center">
                                <i class="ti ti-check text-white" style="font-size:11px"></i>
                            </span>
                        </div>
                        <small class="text-muted">{{ $label }}</small>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Layout Position --}}
            <div class="p-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="fw-semibold mb-0">Layout Position</p>
                    <div class="btn-group btn-group-sm">
                        <label class="btn btn-outline-secondary mb-0">
                            <input type="radio" name="data-layout-position" value="fixed" class="d-none"> Fixed
                        </label>
                        <label class="btn btn-outline-secondary mb-0">
                            <input type="radio" name="data-layout-position" value="scrollable" class="d-none"> Scrollable
                        </label>
                    </div>
                </div>
            </div>

        </div>

        <div class="p-3 border-top">
            <button id="reset-layout" class="btn btn-danger w-100">
                <i class="ti ti-refresh me-1"></i> Reset
            </button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Sincronizar checkmarks y botones activos del customizer
        function syncCustomizer() {
            document.querySelectorAll('#theme-settings-offcanvas input[type=radio]').forEach(function(input) {
                var checkIcon = input.closest('label') && input.closest('label').querySelector('.check-icon');
                var btnLabel = input.matches('label.btn input') ? input.closest('label') : null;
                if (checkIcon) checkIcon.style.display = input.checked ? 'flex' : 'none';
                if (btnLabel) {
                    btnLabel.classList.toggle('btn-primary', input.checked);
                    btnLabel.classList.toggle('btn-outline-secondary', !input.checked);
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar inicial según config
            setTimeout(syncCustomizer, 300);
            // Actualizar al cambiar
            document.querySelectorAll('#theme-settings-offcanvas input[type=radio]').forEach(function(input) {
                input.addEventListener('change', function() { setTimeout(syncCustomizer, 50); });
            });
            // Observar cambios de tema desde fuera
            new MutationObserver(syncCustomizer).observe(document.documentElement, {attributes: true, attributeFilter: ['data-bs-theme','data-sidenav-size','data-layout-position']});
        });
    </script>
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
