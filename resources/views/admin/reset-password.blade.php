<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Nueva contraseña | BiometricIP</title>
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
                    <p class="text-muted mb-0">Establecer nueva contraseña</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">
                    <p class="text-muted small mb-3">
                        Restableciendo contraseña para <strong>{{ old('email', $email ?? '') }}</strong>
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 8 caracteres" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password', this)" tabindex="-1">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repite la contraseña" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password_confirmation', this)" tabindex="-1">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-key me-1"></i> Restablecer contraseña
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
    <script>
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
