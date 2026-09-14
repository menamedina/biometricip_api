<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Login | BiometricIP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" />
    <link id="app-style" href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        html, body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f3f3f4 !important;
            overflow-x: hidden !important;
        }
        body {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .login-outer {
            width: 100% !important;
            max-width: 1100px !important;
            padding: 24px !important;
        }
        .login-card {
            border-radius: 1rem !important;
            overflow: hidden !important;
            box-shadow: 0 4px 24px rgba(0,0,0,.10) !important;
            background: #fff !important;
            min-height: 560px !important;
            display: flex !important;
            flex-direction: row !important;
        }
        .col-form {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            padding: 48px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            background: #fff !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .col-img {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            position: relative !important;
            border-radius: 0 1rem 1rem 0 !important;
            overflow: hidden !important;
            background: #dee2e6 !important;
            min-height: 560px !important;
        }
        .carrusel-slide {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            opacity: 0 !important;
            transition: opacity 1s ease-in-out !important;
            display: block !important;
            text-decoration: none !important;
            cursor: pointer !important;
        }
        .carrusel-slide.active { opacity: 1 !important; }
        .carrusel-slide img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            display: block !important;
        }
        .col-img-placeholder {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
            color: #adb5bd !important;
            font-size: 4rem !important;
        }
        .login-card .form-control {
            height: 44px !important;
        }
        .login-card .input-group-text {
            background: #f8f9fa !important;
            width: 44px !important;
            display: flex !important;
            justify-content: center !important;
        }
        .btn-login {
            border-radius: .5rem !important;
            padding: 11px !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            background: #1ab394 !important;
            border-color: #1ab394 !important;
            color: #fff !important;
        }
        .btn-login:hover {
            background: #17a07d !important;
            border-color: #17a07d !important;
        }
        @media (max-width: 767px) {
            .col-img { display: none !important; }
            .col-form { flex: 0 0 100% !important; max-width: 100% !important; padding: 32px 24px !important; }
        }
    </style>
</head>
<body>
    <div class="login-outer">
        <div class="login-card">

            {{-- Formulario --}}
            <div class="col-form">
                <div class="text-center mb-4">
                    <img src="{{ asset('logos/logo.png') }}" alt="BiometricIP"
                         onerror="this.style.display='none'"
                         style="max-height:120px;max-width:280px;object-fit:contain;box-shadow:none;filter:none;" />
                    <h4 class="fw-bold mt-3 mb-1">Bienvenido a BiometricIP</h4>
                    <p class="text-muted mb-0">Panel de Administración</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fa-solid fa-check-circle me-1"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.login') }}" method="POST" autocomplete="off">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-solid fa-envelope text-muted"></i>
                            </span>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="ejemplo@correo.com"
                                   value="{{ old('email') }}" required autofocus />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa-solid fa-lock text-muted"></i>
                            </span>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="••••••••" required />
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }} />
                            <label class="form-check-label text-muted small" for="remember">Recordar sesión</label>
                        </div>
                        <a href="{{ route('admin.password.request') }}" class="small" style="color:#1ab394;text-decoration:underline;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Iniciar Sesión
                    </button>
                </form>

                <p class="text-center text-muted mt-3 mb-0" style="font-size:.85rem;">
                    &copy; {{ date('Y') }} BiometricIP &mdash; Todos los derechos reservados
                </p>
            </div>

            {{-- Carrusel de imágenes --}}
            <div class="col-img" id="loginCarrusel">
                @if($images->count())
                    @foreach($images as $i => $img)
                        <a href="{{ asset('storage/' . $img->imagen) }}" target="_blank"
                           class="carrusel-slide {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ asset('storage/' . $img->imagen) }}" alt="{{ $img->titulo }}">
                        </a>
                    @endforeach
                @else
                    <div class="col-img-placeholder">
                        <i class="fa-solid fa-fingerprint"></i>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        (function () {
            var slides = document.querySelectorAll('#loginCarrusel .carrusel-slide');
            if (slides.length < 2) return;
            var current = 0;
            setInterval(function () {
                slides[current].classList.remove('active');
                current = (current + 1) % slides.length;
                slides[current].classList.add('active');
            }, 8000);
        })();
    </script>
</body>
</html>
