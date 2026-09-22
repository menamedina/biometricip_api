<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Capacitación</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e8f5f2 0%, #d0ece7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
        }
        .registro-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            max-width: 540px;
            width: 100%;
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #1ab394, #0d7560);
            padding: 28px 32px 20px;
            color: white;
        }
        .card-header-custom .cap-label {
            font-size: 12px;
            opacity: 0.85;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .card-header-custom h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 6px 0 4px;
        }
        .badge-info-cap {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 4px;
        }
        .form-label { font-weight: 600; font-size: 14px; color: #444; }
        .btn-registro {
            background: #1ab394;
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            font-size: 15px;
            transition: background 0.2s;
        }
        .btn-registro:hover { background: #0d7560; color: white; }
    </style>
</head>
<body>

<div class="registro-card">
    <div class="card-header-custom">
        <div class="cap-label">
            @if($capacitacion->tipo_evento === 'asistencia')
                &#x2705; Registro de asistencia
            @else
                &#x1F4DA; Capacitación
            @endif
        </div>
        <h1>{{ $capacitacion->titulo }}</h1>
        @if($empresa)
        <div style="font-size:15px;font-weight:600;opacity:0.9;margin-top:6px;">{{ $empresa->nombre }}</div>
        @endif
        @if($capacitacion->instructor_nombre)
            <span class="badge-info-cap">&#x1F464; {{ $capacitacion->instructor_nombre }}</span>
        @endif
        @if($capacitacion->fecha_capacitacion)
            <div style="font-size:13px; opacity:0.85; margin-top:6px;">
                &#x1F4C5; {{ $capacitacion->fecha_capacitacion->format('d/m/Y \a \l\a\s H:i') }}
            </div>
        @endif
        @if($capacitacion->duracion_horas)
            <div style="font-size:13px; opacity:0.85; margin-top:4px;">
                &#x23F1; Duración: {{ $capacitacion->duracion_horas }}{{ is_numeric($capacitacion->duracion_horas) ? ' hora(s)' : '' }}
            </div>
        @endif
    </div>

    <div class="p-4">
        @if($expirado)
            <div class="text-center py-4">
                <div style="font-size:56px; opacity:0.5;">&#x23F0;</div>
                <h4 class="mt-3 fw-bold text-danger">Este enlace ha expirado</h4>
                <p class="text-muted">El período de registro para esta capacitación ya cerró.</p>
                <p class="text-muted small">Si crees que esto es un error, contacta al organizador.</p>
            </div>
        @else
            {{-- Temas --}}
            @if($capacitacion->temas && count($capacitacion->temas))
                <div class="mb-4">
                    <p class="fw-semibold mb-2" style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.5px;">Temas</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($capacitacion->temas as $tema)
                            <span style="background:#1ab394;color:#fff;border-radius:20px;padding:3px 12px;font-size:13px;font-weight:500;">{{ $tema }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($capacitacion->metodologia)
                <div class="mb-4">
                    <p class="fw-semibold mb-1" style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.5px;">Metodología</p>
                    <p class="mb-0" style="white-space:pre-line;">{{ $capacitacion->metodologia }}</p>
                </div>
            @endif

            {{-- Alertas --}}
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <span>&#x2705;</span><div>{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <span>&#x26A0;&#xFE0F;</span><div>{{ session('error') }}</div>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <span>&#x2139;&#xFE0F;</span><div>{{ session('info') }}</div>
                </div>
            @endif

            @if(!session('success'))

            {{-- PASO 1: Buscar por cédula --}}
            @if(!$cedula)
            <form method="GET" action="">
                <p class="fw-semibold mb-3" style="color:#444;">Ingresa tu número de cédula para continuar:</p>
                @if($errors->has('cedula'))
                    <div class="alert alert-danger">{{ $errors->first('cedula') }}</div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Número de Cédula <span class="text-danger">*</span></label>
                    <input type="text" name="cedula" class="form-control form-control-lg"
                        placeholder="Ej: 1234567890" required autofocus>
                </div>
                <button type="submit" class="btn btn-registro w-100">Continuar &#x27A1;</button>
            </form>

            {{-- PASO 2: Confirmar asistencia --}}
            @else
            <form method="POST" action="{{ route('capacitacion.guardar', [$capacitacion->empresa_id, $capacitacion->token]) }}">
                @csrf

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
                    </div>
                @endif

                <input type="hidden" name="cedula" value="{{ $cedula }}">

                @if($participante && $participante->esConfirmado())
                    <div class="alert alert-info text-center">
                        <strong>{{ $participante->nombre }}</strong>, ya confirmaste tu asistencia.
                    </div>
                @else

                {{-- Aviso según si está programado o es externo --}}
                @if($participante)
                    <div class="mb-3 p-3 rounded" style="background:#e8f7f4;border:1px solid #b2dfdb;">
                        <div style="font-size:13px;color:#0d7560;">
                            &#x2705; <strong>{{ $participante->nombre }}</strong>, estás en la lista de esta capacitación.
                            Confirma tu asistencia con los datos a continuación.
                        </div>
                    </div>
                @else
                    <div class="mb-3 p-3 rounded" style="background:#fff8e1;border:1px solid #ffe082;">
                        <div style="font-size:13px;color:#7a6000;">
                            &#x2139;&#xFE0F; La cédula <strong>{{ $cedula }}</strong> no está en la lista programada.
                            Puedes registrarte igualmente como asistente externo.
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control form-control-lg"
                        value="{{ $participante?->nombre ?? old('nombre') }}"
                        {{ $participante ? 'readonly style=background:#f8f9fa' : '' }}
                        placeholder="Escribe tu nombre completo" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico <span class="text-muted fw-normal">(opcional)</span></label>
                    <input type="email" name="correo" class="form-control form-control-lg"
                        value="{{ $participante?->correo ?? old('correo') }}"
                        placeholder="tucorreo@ejemplo.com">
                </div>

                <div class="mb-4">
                    <label class="form-label">Teléfono <span class="text-muted fw-normal">(opcional)</span></label>
                    <input type="tel" name="telefono" class="form-control form-control-lg"
                        value="{{ $participante?->telefono ?? old('telefono') }}"
                        placeholder="300 123 4567">
                </div>

                <button type="submit" class="btn btn-registro w-100">&#x2705; Confirmar Asistencia</button>
                @endif
            </form>

            <div class="mt-3 text-center">
                <a href="?" class="text-muted small">&#x2190; Ingresar otra cédula</a>
            </div>
            @endif

            @endif

            <div class="text-center mt-3">
                <small class="text-muted">&#x1F512; Tu información es confidencial y solo se usa para el registro de asistencia.</small>
            </div>
        @endif
    </div>
</div>

</body>
</html>
