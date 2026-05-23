<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>AsistenciaQR - Sistema de Registro de Personal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
        }
        body {
            background: #f1f5f9;
            min-height: 100vh;
        }
        .hero-card {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
        }
        .hero-card h1 {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .hero-card p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .feature-icon.purple { background: #ede9fe; color: var(--primary); }
        .feature-icon.green { background: #d1fae5; color: var(--success); }
        .feature-icon.orange { background: #fef3c7; color: #f59e0b; }
        .feature-icon.blue { background: #dbeafe; color: #3b82f6; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/">
                <i class="fa-solid fa-qrcode me-2"></i>AsistenciaQR
            </a>
            <div class="ms-auto">
                <a href="{{ route('admin.login.show') }}" class="btn btn-primary">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Acceder
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="hero-card mb-5">
            <h1 class="mb-3"><i class="fa-solid fa-qrcode me-2"></i>AsistenciaQR</h1>
            <p class="mb-4">Sistema inteligente de registro de asistencia con validación QR, geolocalización y reconocimiento facial.</p>
            <a href="{{ route('admin.login.show') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                <i class="fa-solid fa-arrow-right me-2"></i>Panel de Administración
            </a>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon purple"><i class="fa-solid fa-qrcode"></i></div>
                        <h5 class="fw-bold">QR Dinámico</h5>
                        <p class="text-muted small">Códigos rotativos cada 30 segundos. Evita suplantación de identidad.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon green"><i class="fa-solid fa-location-crosshairs"></i></div>
                        <h5 class="fw-bold">Geocerca</h5>
                        <p class="text-muted small">Valida que el empleado esté dentro del radio permitido de la oficina.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon orange"><i class="fa-solid fa-shield-halved"></i></div>
                        <h5 class="fw-bold">Anti-fraude</h5>
                        <p class="text-muted small">QR + GPS + foto evidencia. Previene marcajes remotos.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon blue"><i class="fa-solid fa-file-excel"></i></div>
                        <h5 class="fw-bold">Reportes</h5>
                        <p class="text-muted small">Exportación a CSV. Reportes de horas, tardanzas y ausentismo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-muted small">
        AsistenciaQR v{{ app()->version() }} &mdash; Laravel {{ app()->version() }}
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
