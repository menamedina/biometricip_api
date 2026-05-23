<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Login | AsistenciaQR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <link href="{{ asset('assets/css/vendors.min.css') }}" rel="stylesheet" />
    <link id="app-style" href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>
    <div class="d-flex align-items-center justify-content-center vh-100 bg-light">
        <div class="card shadow" style="width: 400px; max-width: 90vw;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary"><i class="fa-solid fa-qrcode me-2"></i>AsistenciaQR</h3>
                    <p class="text-muted">Panel de Administración</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@asistenciaqr.com" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Tu contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
