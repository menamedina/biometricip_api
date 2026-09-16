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
    @stack('styles')
    <style>
        /* ── Tooltips en botones disabled ────────────────── */
        .btn:disabled, .btn[disabled] { pointer-events: auto; cursor: not-allowed; }

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
                    <span class="fw-semibold text-dark ms-2 d-none d-md-inline" style="font-size:1rem;">
                        {{ auth()->user()->empresa?->nombre ?? 'BiometricIP' }}
                    </span>
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
                                @php
                                    $navThumbnail = \App\Models\UserImagen::where('user_id', auth()->id())->value('imagen_thumbnail');
                                @endphp
                                @if($navThumbnail)
                                    <img src="{{ $navThumbnail }}" alt="{{ auth()->user()->name }}"
                                         style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:2px solid #1ab394;">
                                @else
                                    <span class="avatar-title bg-primary rounded-circle fw-bold">{{ substr(auth()->user()->name ?? 'A', 0, 2) }}</span>
                                @endif
                            </span>
                            <span class="d-lg-block d-none">
                                <span class="account-user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="px-3 pt-2 pb-1">
                                <span class="badge bg-primary-subtle text-primary fw-semibold">
                                    {{ auth()->user()->getRoleNames()->first() ?? auth()->user()->role }}
                                </span>
                            </div>
                            <div class="dropdown-divider mt-1"></div>
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
                            <span class="sidenav-user-name fw-bold" title="{{ auth()->user()->name ?? 'Admin' }}">{{ Str::limit(auth()->user()->name ?? 'Admin', 20) }}</span>
                            <span class="fs-12 fw-semibold d-block">{{ auth()->user()->getRoleNames()->first() ?? auth()->user()->role }}</span>
                        </div>
                    </div>
                </div>

                <div id="sidenav-menu">
                    @php $spatieRole = auth()->user()->getRoleNames()->first(); @endphp
                    <ul class="side-nav">
                        <li class="side-nav-title mt-2">Menú Principal</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </li>

                        {{-- ── Administración ──────────────────────────────── --}}
                        @canany(['sedes.ver','empleados.ver','visitantes.ver','dispositivos.ver'])
                        <li class="side-nav-title mt-2">Administración</li>
                        @endcanany
                        @can('sedes.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sedes.index') }}" class="side-nav-link {{ request()->routeIs('admin.sedes.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-building"></i></span>
                                <span class="menu-text">Sedes</span>
                            </a>
                        </li>
                        @endcan
                        @can('empleados.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.empleados.index') }}" class="side-nav-link {{ request()->routeIs('admin.empleados.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-users"></i></span>
                                <span class="menu-text">Empleados</span>
                            </a>
                        </li>
                        @endcan
                        @can('visitantes.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.visitantes.index') }}" class="side-nav-link {{ request()->routeIs('admin.visitantes.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-user-check"></i></span>
                                <span class="menu-text">Visitantes</span>
                            </a>
                        </li>
                        @endcan
                        @can('dispositivos.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.dispositivos.index') }}" class="side-nav-link {{ request()->routeIs('admin.dispositivos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-fingerprint"></i></span>
                                <span class="menu-text">Dispositivos</span>
                            </a>
                        </li>
                        @endcan

                        {{-- ── Asistencia ──────────────────────────────────── --}}
                        @canany(['asistencia.ver','reportes.ver','resumen_mensual.ver','permisos.ver','capacitaciones.ver'])
                        <li class="side-nav-title mt-2">Asistencia</li>
                        @endcanany
                        @can('asistencia.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.attendance.index') }}" class="side-nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-clock"></i></span>
                                <span class="menu-text">Registros</span>
                            </a>
                        </li>
                        @endcan
                        @can('reportes.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.resumen.index') }}" class="side-nav-link {{ request()->routeIs('admin.resumen.index') || request()->routeIs('admin.resumen.records') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-calendar-stats"></i></span>
                                <span class="menu-text">Resumen Marcación</span>
                            </a>
                        </li>
                        @endcan
                        @can('resumen_mensual.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.resumen-mensual.index') }}" class="side-nav-link {{ request()->routeIs('admin.resumen-mensual.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-table-column"></i></span>
                                <span class="menu-text">Resumen Mensual</span>
                            </a>
                        </li>
                        @endcan
                        @can('permisos.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.permisos.index') }}" class="side-nav-link {{ request()->routeIs('admin.permisos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-file-certificate"></i></span>
                                <span class="menu-text">Permisos / Ausencias</span>
                            </a>
                        </li>
                        @endcan
                        @can('capacitaciones.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.capacitaciones.index') }}" class="side-nav-link {{ request()->routeIs('admin.capacitaciones.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-school"></i></span>
                                <span class="menu-text">Capacitaciones</span>
                            </a>
                        </li>
                        @endcan

                        {{-- ── Organización ────────────────────────────────── --}}
                        @canany(['departamentos.ver','empleadores.ver'])
                        <li class="side-nav-title mt-2">Organización</li>
                        @endcanany
                        @can('departamentos.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.departamentos.index') }}" class="side-nav-link {{ request()->routeIs('admin.departamentos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-sitemap"></i></span>
                                <span class="menu-text">Deptos. y Cargos</span>
                            </a>
                        </li>
                        @endcan
                        @can('empleadores.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.empleadores.index') }}" class="side-nav-link {{ request()->routeIs('admin.empleadores.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-briefcase"></i></span>
                                <span class="menu-text">Empleadores</span>
                            </a>
                        </li>
                        @endcan

                        {{-- ── Empresa ─────────────────────────────────────── --}}
                        @can('empresa.ver')
                        <li class="side-nav-title mt-2">Empresa</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.empresas.index') }}" class="side-nav-link {{ request()->routeIs('admin.empresas.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-building-skyscraper"></i></span>
                                <span class="menu-text">Mi Empresa</span>
                            </a>
                        </li>
                        @endcan

                        {{-- ── Comunicación ────────────────────────────────── --}}
                        @can('notificaciones.ver')
                        <li class="side-nav-title mt-2">Comunicación</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.notificaciones.index') }}" class="side-nav-link {{ request()->routeIs('admin.notificaciones.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-bell-ringing"></i></span>
                                <span class="menu-text">Notificaciones</span>
                            </a>
                        </li>
                        @endcan

                        {{-- ── Configuración ───────────────────────────────── --}}
                        @canany(['horarios.ver','festivos.ver','roles.ver','ia.ver'])
                        <li class="side-nav-title mt-2">Configuración</li>
                        @endcanany
                        @can('horarios.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.horarios.index') }}" class="side-nav-link {{ request()->routeIs('admin.horarios.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-clock-play"></i></span>
                                <span class="menu-text">Horarios</span>
                            </a>
                        </li>
                        @endcan
                        @can('festivos.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.festivos.index') }}" class="side-nav-link {{ request()->routeIs('admin.festivos.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-calendar-x"></i></span>
                                <span class="menu-text">Festivos</span>
                            </a>
                        </li>
                        @endcan
                        @if(auth()->user()->can('roles.ver'))
                        <li class="side-nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="side-nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-shield-lock"></i></span>
                                <span class="menu-text">Roles y Permisos</span>
                            </a>
                        </li>
                        @endif
                        @can('ia.ver')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.ai.config') }}" class="side-nav-link {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-robot"></i></span>
                                <span class="menu-text">Asistente IA</span>
                            </a>
                        </li>
                        @endcan

                        @if(auth()->user()->admin_tenant ?? false)
                        <li class="side-nav-title mt-2">Super Admin</li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.tenants.index') }}" class="side-nav-link {{ request()->routeIs('admin.tenants.index') || request()->routeIs('admin.tenants.create') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-database"></i></span>
                                <span class="menu-text">Tenants</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.tenants.tablas') }}" class="side-nav-link {{ request()->routeIs('admin.tenants.tablas') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-table"></i></span>
                                <span class="menu-text">Config. Tablas</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.login-images.index') }}" class="side-nav-link {{ request()->routeIs('admin.login-images.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-photo"></i></span>
                                <span class="menu-text">Imágenes Login</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="content-page d-flex flex-column" style="min-height:calc(100vh - 70px);">
            <div class="container-fluid flex-grow-1">
                @yield('content')
            </div>
            <footer class="d-flex justify-content-between align-items-center px-3 py-2 mt-auto" style="font-size:12px;border-top:1px solid #dee2e6;color:#6c757d;background:#fff;">
                <span><strong>Copyright</strong> &copy; {{ date('Y') }} BiometricIP &middot; Todos los derechos reservados</span>
                <span>Bienvenidos a <strong style="color:#1ab394;">Biometric</strong><strong>IP</strong>.</span>
            </footer>
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

    @stack('scripts')

    {{-- ── Widget Chat IA ──────────────────────────────────────────── --}}

    @if(auth()->check() && auth()->user()->can('ia.chat'))
    {{-- Panel del chat — posicionado independiente del botón --}}
    <div id="aiChatPanel" style="display:none;position:fixed;bottom:110px;right:24px;z-index:1051;width:340px;border-radius:12px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.18);background:#fff;flex-direction:column;">
        {{-- Header --}}
        <div style="background:#1ab394;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;">
            <div class="d-flex align-items-center gap-2">
                <i class="ti ti-robot text-white" style="font-size:1.2rem;"></i>
                <span class="text-white fw-semibold" style="font-size:.95rem;">Asistente BiometricIP</span>
            </div>
            <button onclick="toggleChat()" style="background:none;border:none;color:#fff;font-size:1.1rem;line-height:1;cursor:pointer;">
                <i class="ti ti-x"></i>
            </button>
        </div>
        {{-- Mensajes --}}
        <div id="aiMessages" style="overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;height:320px;">
            <div class="ai-msg ai-msg-bot">
                Hola, soy el asistente de BiometricIP. ¿En qué te puedo ayudar?
            </div>
        </div>
        {{-- Input --}}
        <div style="padding:10px 12px;border-top:1px solid #e9ecef;display:flex;gap:8px;">
            <input type="text" id="aiInput" class="form-control form-control-sm"
                placeholder="Escribe un mensaje..."
                onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault();sendAiMessage();}">
            <button onclick="sendAiMessage()" style="background:#1ab394;border:none;border-radius:6px;color:#fff;padding:0 14px;cursor:pointer;">
                <i class="ti ti-send"></i>
            </button>
        </div>
    </div>

    {{-- Botón flotante — fijo en esquina inferior derecha --}}
    <button onclick="toggleChat()" id="aiFloatBtn"
        style="position:fixed;bottom:46px;right:20px;z-index:1050;width:48px;height:48px;border-radius:50%;background:#1ab394;border:none;color:#fff;font-size:1.3rem;box-shadow:0 4px 12px rgba(26,179,148,.5);display:flex;align-items:center;justify-content:center;cursor:pointer;">
        <i class="ti ti-message-chatbot"></i>
    </button>
    @endif

    <style>
    .ai-msg { padding:8px 12px; border-radius:10px; font-size:.85rem; max-width:85%; line-height:1.4; }
    .ai-msg-bot { background:#f0faf8; color:#1a3c34; align-self:flex-start; border-bottom-left-radius:2px; }
    .ai-msg-user { background:#1ab394; color:#fff; align-self:flex-end; border-bottom-right-radius:2px; }
    .ai-msg-typing { background:#f0faf8; color:#999; align-self:flex-start; font-style:italic; }
    </style>

    <script>
    const AI_CSRF = '{{ csrf_token() }}';
    let aiMessages = [];
    let aiPanelOpen = false;

    function toggleChat() {
        aiPanelOpen = !aiPanelOpen;
        const panel = document.getElementById('aiChatPanel');
        panel.style.display = aiPanelOpen ? 'flex' : 'none';
        panel.style.flexDirection = 'column';
        if (aiPanelOpen) document.getElementById('aiInput').focus();
    }

    function appendMsg(role, text) {
        const el = document.getElementById('aiMessages');
        const div = document.createElement('div');
        div.className = 'ai-msg ' + (role === 'user' ? 'ai-msg-user' : 'ai-msg-bot');
        div.textContent = text;
        el.appendChild(div);
        el.scrollTop = el.scrollHeight;
        return div;
    }

    async function sendAiMessage() {
        const inp = document.getElementById('aiInput');
        const text = inp.value.trim();
        if (!text) return;

        inp.value = '';
        inp.disabled = true;

        appendMsg('user', text);
        aiMessages.push({ role: 'user', content: text });

        // Indicador de escritura
        const typing = document.getElementById('aiMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'ai-msg ai-msg-typing';
        typingDiv.textContent = 'Escribiendo...';
        typing.appendChild(typingDiv);
        typing.scrollTop = typing.scrollHeight;

        try {
            const res = await fetch('/admin/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': AI_CSRF },
                body: JSON.stringify({ messages: aiMessages }),
            });
            const data = await res.json();
            typingDiv.remove();

            const reply = res.ok ? (data.reply || '—') : (data.message || 'Error al conectar.');
            appendMsg('assistant', reply);
            if (res.ok) aiMessages.push({ role: 'assistant', content: reply });

        } catch (e) {
            typingDiv.remove();
            appendMsg('assistant', 'Error de conexión.');
        }

        inp.disabled = false;
        inp.focus();
    }
    </script>

</body>
</html>
