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
            height: 100%;
            margin: 0;
            background-color: #f3f3f4;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-outer {
            width: 100%;
            max-width: 1100px;
            padding: 24px;
        }
        .login-card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
            background: #fff;
            min-height: 560px;
            display: flex;
        }
        .col-form {
            flex: 0 0 50%;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .col-img {
            flex: 0 0 50%;
            position: relative;
            border-radius: 0 1rem 1rem 0;
            overflow: hidden;
            background: #dee2e6;
        }
        .carrusel-slide {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        .carrusel-slide.active { opacity: 1; }
        .col-img-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #adb5bd;
            font-size: 4rem;
        }
        .form-control {
            height: 44px;
        }
        .input-group-text {
            background: #f8f9fa;
            width: 44px;
            justify-content: center;
        }
        .btn-login {
            border-radius: .5rem;
            padding: 11px;
            font-weight: 600;
            font-size: 1rem;
            background: #1ab394;
            border-color: #1ab394;
        }
        .btn-login:hover {
            background: #17a07d;
            border-color: #17a07d;
        }
        @media (max-width: 767px) {
            .col-img { display: none; }
            .col-form { flex: 0 0 100%; padding: 32px 24px; }
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
                        <div class="carrusel-slide {{ $i === 0 ? 'active' : '' }}"
                             style="background-image:url('{{ asset('storage/' . $img->imagen) }}')"></div>
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
            }, 5000);
        })();
    </script>
</body>
</html>
