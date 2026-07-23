<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Recuperar contraseña | BiometricIP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" />
    <link id="app-style" href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>
    <div class="d-flex align-items-center justify-content-center vh-100 bg-light">
        <div class="card shadow" style="width: 420px; max-width: 90vw;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('logos/logo.png') }}" alt="BiometricIP" class="mb-3" style="max-height: 120px; max-width: 280px; object-fit: contain;">
                    <p class="text-muted mb-0">Recuperar contraseña</p>
                </div>

                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <p class="text-muted small mb-3">
                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                </p>

                <form action="{{ route('admin.password.email') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@biometricip.com" value="{{ old('email') }}" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Enviar enlace de recuperación
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('admin.login.show') }}" class="text-muted small">
                        <i class="fa-solid fa-arrow-left me-1"></i> Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
