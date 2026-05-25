<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Solicitud de Eliminación de Datos - BiometricIP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        body { background: #f8fafc; }
        .navbar-brand img { height: 40px; object-fit: contain; }
        .form-card {
            max-width: 620px;
            margin: 3rem auto;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .form-card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            padding: 2rem 2rem 1.5rem;
        }
        .form-card-header h2 { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .form-card-header p  { opacity: .85; font-size: .9rem; margin: .5rem 0 0; }
        .form-card-body { padding: 2rem; }
        .notice { background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: .5rem; padding: 1rem 1.25rem; font-size: .88rem; color: #78350f; }
        footer { background: #1e293b; color: #94a3b8; text-align: center; padding: 1.25rem; font-size: .85rem; margin-top: 3rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('logos/logo.png') }}" alt="BiometricIP">
        </a>
        <a href="{{ route('privacy') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Política de Privacidad
        </a>
    </div>
</nav>

<div class="container px-3">
    <div class="form-card">
        <div class="form-card-header">
            <h2><i class="fa-solid fa-trash-can me-2"></i>Solicitud de Eliminación de Datos</h2>
            <p>Derecho de Supresión · HABEAS DATA (Ley 1581/2012) · CCPA Right to Delete</p>
        </div>

        <div class="form-card-body">

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                    <div>
                        <strong>Solicitud recibida.</strong> Hemos registrado su solicitud con el número
                        <strong>#{{ session('ticket') }}</strong>. Le responderemos al correo indicado
                        dentro de los plazos legales establecidos (15 días hábiles en Colombia / 45 días en EE. UU.).
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!session('success'))
            <div class="notice mb-4">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                <strong>Importante:</strong> La eliminación de sus datos es permanente e irreversible.
                Una vez procesada la solicitud, su cuenta y todos los registros asociados serán eliminados
                del sistema, excepto aquellos que la ley obligue a conservar (ej. registros laborales por 5 años).
            </div>

            <form action="{{ route('data-deletion.submit') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" placeholder="Ej. Juan Pérez" required>
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Número de documento / ID <span class="text-danger">*</span></label>
                    <input type="text" name="documento" class="form-control @error('documento') is-invalid @enderror"
                           value="{{ old('documento') }}" placeholder="Ej. 1234567890" required>
                    @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo electrónico registrado <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">País de residencia <span class="text-danger">*</span></label>
                    <select name="pais" class="form-select @error('pais') is-invalid @enderror" required>
                        <option value="">Seleccione...</option>
                        <option value="CO" {{ old('pais') == 'CO' ? 'selected' : '' }}>Colombia</option>
                        <option value="US" {{ old('pais') == 'US' ? 'selected' : '' }}>Estados Unidos</option>
                        <option value="otro" {{ old('pais') == 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('pais')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de solicitud <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                        <option value="">Seleccione...</option>
                        <option value="eliminacion_total" {{ old('tipo') == 'eliminacion_total' ? 'selected' : '' }}>Eliminar cuenta y todos los datos</option>
                        <option value="eliminacion_parcial" {{ old('tipo') == 'eliminacion_parcial' ? 'selected' : '' }}>Eliminar solo datos biométricos (fotos)</option>
                        <option value="eliminacion_gps" {{ old('tipo') == 'eliminacion_gps' ? 'selected' : '' }}>Eliminar solo datos de geolocalización</option>
                        <option value="revocacion" {{ old('tipo') == 'revocacion' ? 'selected' : '' }}>Revocar autorización de tratamiento</option>
                    </select>
                    @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Motivo adicional (opcional)</label>
                    <textarea name="motivo" class="form-control" rows="3"
                              placeholder="Describa brevemente el motivo de su solicitud...">{{ old('motivo') }}</textarea>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input @error('confirma') is-invalid @enderror"
                               type="checkbox" name="confirma" id="confirma" value="1" required>
                        <label class="form-check-label small" for="confirma">
                            Confirmo que soy el titular de los datos y entiendo que esta acción es
                            <strong>irreversible</strong>. He leído la
                            <a href="{{ route('privacy') }}" target="_blank">Política de Privacidad</a>.
                        </label>
                        @error('confirma')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 fw-semibold">
                    <i class="fa-solid fa-trash-can me-2"></i>Enviar Solicitud de Eliminación
                </button>
            </form>
            @endif

            <p class="text-muted small text-center mt-4 mb-0">
                Su solicitud será procesada conforme a la Ley 1581 de 2012 (Colombia) y la CCPA (EE. UU.).<br>
                Plazo máximo: <strong>15 días hábiles</strong> (CO) / <strong>45 días</strong> (EE. UU.).
            </p>
        </div>
    </div>
</div>

<footer>
    &copy; {{ date('Y') }} Innovasoftip SAS &mdash; BiometricIP &mdash;
    <a href="{{ route('privacy') }}" class="text-info">Política de Privacidad</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
