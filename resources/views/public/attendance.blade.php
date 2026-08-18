<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Asistencia — {{ $sede->nombre }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }
        body {
            background: #fff url('{{ asset('img/Fondo_QR.png') }}') center top / contain no-repeat;
        }
        .card-form {
            width: 100%;
            min-height: 100vh;
            border-radius: 0;
            box-shadow: none;
            border: none;
            background: transparent;
        }
        .sede-header {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            border-radius: 0;
            padding: 28px 20px 22px;
            color: #fff;
        }
        .sede-header h5 { font-size: 1rem; opacity: .85; margin-bottom: 4px; }
        .sede-header h3 { font-size: 1.5rem; font-weight: 700; margin: 0; }

        /* Botones tipo usuario (Empleado / Visitante) */
        .user-type-btn {
            flex: 1;
            border-radius: 16px !important;
            padding: 24px 8px;
            font-size: 1.1rem;
            font-weight: 700;
            border-width: 2px;
            line-height: 1.3;
            min-height: 110px;
            transition: all .15s;
            touch-action: manipulation;
        }
        .user-type-btn i {
            display: block;
            font-size: 2.4rem;
            margin-bottom: 10px;
        }

        /* Botones Entrada / Salida */
        .tipo-btn {
            flex: 1;
            border-radius: 12px !important;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 16px 8px;
            min-height: 60px;
            touch-action: manipulation;
        }
        .tipo-btn.active { box-shadow: 0 2px 10px rgba(0,0,0,.2); }

        #photoPreview {
            width: 100%; max-height: 260px; border-radius: 12px;
            object-fit: cover; display: none; margin-top: 12px;
            border: 2px solid #4F46E5;
        }
        .photo-label {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border: 2px dashed #c7d2fe; border-radius: 14px; padding: 28px 12px;
            cursor: pointer; color: #6366f1; background: #eef2ff;
            transition: background .2s; touch-action: manipulation;
            min-height: 110px;
        }
        .photo-label:active { background: #e0e7ff; }
        .photo-label i { font-size: 2.6rem; margin-bottom: 8px; }
        .photo-label span { font-size: 1rem; font-weight: 600; }

        /* Inputs más grandes para táctil */
        .form-control, .form-control-lg {
            font-size: 1.05rem !important;
            padding: 14px 14px !important;
            border-radius: 10px !important;
            min-height: 52px;
        }
        /* Mayúsculas en todos los campos de texto */
        input.form-control[type="text"],
        input.form-control[type="tel"],
        input.form-control[type="number"],
        input.form-control-lg {
            text-transform: uppercase;
        }
        input.form-control::placeholder,
        input.form-control-lg::placeholder {
            text-transform: none;
        }

        /* Botón principal grande */
        #btnSubmit {
            font-size: 1.15rem;
            font-weight: 700;
            padding: 18px;
            border-radius: 14px !important;
            min-height: 60px;
            touch-action: manipulation;
        }

        #resultBox { display: none; border-radius: 12px; }
        .spinner-border { width: 1.5rem; height: 1.5rem; }
        .step-back { cursor: pointer; color: #4F46E5; font-size: .9rem; touch-action: manipulation; }
        .section-divider {
            font-size: .75rem; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: #9ca3af; margin: 18px 0 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-divider::before, .section-divider::after {
            content: ''; flex: 1; height: 1px; background: #e5e7eb;
        }
    </style>
</head>
<body>
<div class="card card-form">
    <div class="sede-header">
        <h5><i class="fa-solid fa-building me-1"></i> Registro de Asistencia</h5>
        <h3>{{ $sede->nombre }}</h3>
    </div>
    <div class="card-body p-4" style="padding: 24px 20px !important;">

        <div id="resultBox" class="alert py-3 mb-3"></div>

        {{-- ── PASO 1: ¿Empleado o Visitante? ──────────────────────────── --}}
        <div id="step1">
            <p class="fw-semibold text-center mb-1" style="font-size:1.05rem;">¿Cómo deseas registrarte?</p>
            <p class="text-muted text-center small mb-4">Selecciona tu tipo de ingreso</p>
            <div style="display:flex; flex-direction:column; gap:16px;">
                <button type="button" class="btn btn-outline-primary user-type-btn w-100" onclick="selectTipoUsuario('empleado')">
                    <i class="fa-solid fa-id-badge"></i>Empleado
                </button>
                <button type="button" class="btn btn-outline-secondary user-type-btn w-100" onclick="selectTipoUsuario('visitante')">
                    <i class="fa-solid fa-user-clock"></i>Visitante
                </button>
            </div>
        </div>

        {{-- ── PASO 2: Formulario ───────────────────────────────────────── --}}
        <div id="step2" style="display:none;">
            <a class="step-back d-inline-flex align-items-center gap-1 mb-3" onclick="volverPaso1()">
                <i class="fa-solid fa-chevron-left"></i> <span id="step2Label"></span>
            </a>

            {{-- Entrada / Salida --}}
            <div id="tipoBtns" style="display:flex; flex-direction:column; gap:16px; margin-bottom:24px;">
                <button type="button" class="btn btn-outline-success tipo-btn w-100" id="btnEntrada" onclick="setTipo('entrada')">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Entrada
                </button>
                <button type="button" class="btn btn-outline-danger tipo-btn w-100" id="btnSalida" onclick="setTipo('salida')">
                    <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Salida
                </button>
            </div>

            {{-- Cuerpo del formulario — se muestra tras elegir tipo --}}
            <div id="formBody" class="d-none">

                {{-- Cédula — siempre primero --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Número de cédula <span class="text-danger">*</span></label>
                    <input type="number" id="cedula" class="form-control form-control-lg"
                           placeholder="Ej: 1234567890" inputmode="numeric" autocomplete="off">
                    <div class="invalid-feedback" id="cedulaError"></div>
                </div>

                {{-- Aviso políticas de datos — solo visitante entrada --}}
                <div id="politicasBox" class="d-none mb-4" style="background:#f0f4ff;border-radius:12px;padding:16px 18px;border:1px solid #c7d2fe;">
                    <p style="font-size:.82rem;color:#374151;margin-bottom:10px;line-height:1.55;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <strong>Tratamiento de datos personales</strong><br>
                        Sus datos serán tratados conforme a la <strong>Ley 1581 de 2012</strong>. La fotografía facial, datos de identidad y de contacto se recolectan exclusivamente para el control de acceso y seguridad de las instalaciones.
                    </p>
                    <div class="form-check" style="align-items:flex-start;display:flex;gap:10px;">
                        <input class="form-check-input mt-1" type="checkbox" id="chkPoliticas" onchange="checkReady()"
                               style="width:20px;height:20px;min-width:20px;border-radius:5px;border:2px solid #6366f1;cursor:pointer;flex-shrink:0;">
                        <label class="form-check-label" for="chkPoliticas" style="font-size:.85rem;color:#374151;cursor:pointer;line-height:1.5;">
                            He leído y acepto las
                            <a href="{{ route('public.politicas') }}" target="_blank" style="color:#4F46E5;font-weight:600;text-decoration:underline;">
                                Políticas de Tratamiento de Datos Personales
                            </a>
                            y autorizo el uso de mis datos para los fines descritos.
                        </label>
                    </div>
                    <div class="text-danger small mt-1" id="politicasError" style="display:none;">Debes aceptar las políticas para continuar.</div>
                </div>

                {{-- Continuar (solo visitante + entrada) --}}
                <div id="cedulaContinuarBar" class="d-none mb-4">
                    <button type="button" class="btn btn-primary btn-lg w-100" id="btnContinuar" onclick="continuarVisitante()"
                            style="font-size:1.15rem;font-weight:700;padding:18px;border-radius:14px;min-height:60px;touch-action:manipulation;">
                        <span id="btnContinuarText">Continuar <i class="fa-solid fa-arrow-right ms-1"></i></span>
                        <span id="btnContinuarSpinner" class="d-none">
                            <span class="spinner-border me-1"></span> Buscando...
                        </span>
                    </button>
                </div>

                {{-- Campos extra: solo visitante entrada --}}
                <div id="visitanteFields" class="d-none">
                    <div class="section-divider">Datos del visitante</div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" id="nombre" class="form-control" placeholder="Ej: Juan Pérez" autocomplete="off">
                        <div class="invalid-feedback" id="nombreError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                        <input type="tel" id="telefono" class="form-control" placeholder="Ej: 3001234567" inputmode="numeric" autocomplete="off">
                        <div class="invalid-feedback" id="telefonoError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">EPS <span class="text-danger">*</span></label>
                        <input type="text" id="eps" class="form-control" placeholder="Ej: Sura" autocomplete="off">
                        <div class="invalid-feedback" id="epsError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ARL <span class="text-danger">*</span></label>
                        <input type="text" id="arl" class="form-control" placeholder="Ej: Positiva" autocomplete="off">
                        <div class="invalid-feedback" id="arlError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Empresa <span class="text-danger">*</span></label>
                        <input type="text" id="empresa" class="form-control" placeholder="Ej: Servicios ABC S.A.S" autocomplete="off">
                        <div class="invalid-feedback" id="empresaError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Placa del vehículo</label>
                        <input type="text" id="placa" class="form-control" placeholder="Ej: ABC-123" autocomplete="off">
                    </div>

                    {{-- Aviso cuando se pre-llenaron datos previos --}}
                    <div id="visitanteInfoBox" class="d-none alert alert-info d-flex align-items-start gap-2 py-2 px-3 mb-3" style="font-size:.9rem;border-radius:10px;">
                        <i class="fa-solid fa-circle-info mt-1"></i>
                        <span>Encontramos tu registro anterior. Verifica que la información sea correcta antes de continuar.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">¿A quién visita? <span class="text-danger">*</span></label>
                        <input type="text" id="personaVisita" class="form-control" placeholder="Nombre del empleado o área" autocomplete="off">
                        <div class="invalid-feedback" id="personaVisitaError"></div>
                    </div>

                    <div class="section-divider"></div>
                </div>

                {{-- Foto --}}
                <div class="mb-4" id="photoSection">
                    <label class="form-label fw-semibold" id="photoLabel">Foto <span class="text-danger">*</span></label>
                    <label class="photo-label" for="photoInput">
                        <i class="fa-solid fa-camera"></i>
                        <span id="photoLabelText">Toca para abrir la cámara</span>
                    </label>
                    <input type="file" id="photoInput" accept="image/*" capture="user" class="d-none">
                    <img id="photoPreview" alt="Vista previa">
                    <div class="text-danger small mt-1" id="photoError"></div>
                </div>

                <button type="button" class="btn btn-primary btn-lg w-100" id="btnSubmit" onclick="submitForm()" disabled>
                    <span id="btnText"><i class="fa-solid fa-check me-1"></i> Registrar</span>
                    <span id="btnSpinner" class="d-none">
                        <span class="spinner-border me-1"></span> Registrando...
                    </span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
const URL_POST             = '{{ route("public.attendance.store", [$webToken, $sedeCode, $token]) }}';
const URL_BUSCAR_VISITANTE = '{{ route("public.attendance.buscar-visitante", [$webToken, $sedeCode, $token]) }}';
const SEDE_LAT    = {{ $sede->lat }};
const SEDE_LNG    = {{ $sede->lng }};
const SEDE_RADIO  = {{ $sede->radio_mts }};

let tipoUsuario        = null;
let tipoSeleccionado   = null;
let fotoBase64         = null;
let userLat            = null;
let userLng            = null;
let visitanteLookupOk  = false; // true cuando ya pasó el paso de cédula lookup

// ── Paso 1 ────────────────────────────────────────────────────────────────────
function selectTipoUsuario(tipo) {
    tipoUsuario      = tipo;
    tipoSeleccionado = null;
    fotoBase64       = null;

    // Limpiar campos, errores y re-habilitar cédula
    ['nombre','cedula','telefono','eps','arl','empresa','placa','personaVisita'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.value = ''; el.classList.remove('is-invalid'); el.disabled = false; }
    });
    document.getElementById('visitanteInfoBox').classList.add('d-none');
    ['nombreError','cedulaError','personaVisitaError','photoError'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '';
    });
    document.getElementById('photoInput').value = '';
    document.getElementById('photoLabelText').textContent = 'Toca para abrir la cámara';
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('politicasBox').classList.add('d-none');
    document.getElementById('chkPoliticas').checked = false;
    document.getElementById('politicasError').style.display = 'none';

    // Resetear botones Entrada/Salida
    document.getElementById('tipoBtns').classList.remove('d-none');
    document.getElementById('btnEntrada').className = 'btn btn-outline-success tipo-btn';
    document.getElementById('btnSalida').className  = 'btn btn-outline-danger tipo-btn';

    // Ambos tipos: ocultar form hasta elegir Entrada o Salida
    document.getElementById('formBody').classList.add('d-none');
    document.getElementById('visitanteFields').classList.add('d-none');
    document.getElementById('photoSection').classList.remove('d-none');

    document.getElementById('btnSubmit').disabled = true;
    document.getElementById('resultBox').style.display = 'none';
    document.getElementById('step2Label').textContent = tipo === 'empleado' ? 'Soy Empleado' : 'Soy Visitante';
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
}

function volverPaso1() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
    document.getElementById('resultBox').style.display = 'none';
}

function nuevoRegistro() {
    document.getElementById('resultBox').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
    ['nombre','cedula','telefono','eps','arl','empresa','placa','personaVisita'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.value = ''; el.disabled = false; }
    });
    document.getElementById('visitanteInfoBox').classList.add('d-none');
    tipoUsuario       = null;
    tipoSeleccionado  = null;
    fotoBase64        = null;
    userLat           = null;
    userLng           = null;
    visitanteLookupOk = false;
}

// ── Lookup visitante por cédula ───────────────────────────────────────────────
async function continuarVisitante() {
    const cedula = document.getElementById('cedula').value.trim();
    if (cedula.length < 5) {
        document.getElementById('cedula').classList.add('is-invalid');
        document.getElementById('cedulaError').textContent = 'Ingresa un número de cédula válido.';
        return;
    }
    document.getElementById('cedula').classList.remove('is-invalid');
    document.getElementById('cedulaError').textContent = '';

    if (!document.getElementById('chkPoliticas').checked) {
        document.getElementById('politicasError').style.display = 'block';
        document.getElementById('politicasBox').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    document.getElementById('politicasError').style.display = 'none';

    // Mostrar spinner
    document.getElementById('btnContinuarText').classList.add('d-none');
    document.getElementById('btnContinuarSpinner').classList.remove('d-none');
    document.getElementById('btnContinuar').disabled = true;

    let datosPreviosEncontrados = false;
    try {
        const res  = await fetch(URL_BUSCAR_VISITANTE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cedula }),
        });
        const data = await res.json();

        const camposPrevios = ['nombre','telefono','eps','arl','empresa'];

        if (data.found) {
            datosPreviosEncontrados = true;
            // Pre-llenar campos editables; cédula queda deshabilitada
            document.getElementById('cedula').disabled = true;
            document.getElementById('nombre').value   = data.nombre   || '';
            document.getElementById('telefono').value = data.telefono || '';
            document.getElementById('eps').value      = data.eps      || '';
            document.getElementById('arl').value      = data.arl      || '';
            document.getElementById('empresa').value  = data.empresa  || '';
            document.getElementById('placa').value    = data.placa    || '';
            document.getElementById('personaVisita').value = '';
            document.getElementById('visitanteInfoBox').classList.remove('d-none');
        } else {
            // Visitante nuevo: campos vacíos
            camposPrevios.forEach(id => { document.getElementById(id).value = ''; });
            document.getElementById('visitanteInfoBox').classList.add('d-none');
        }
    } catch (_) {
        // Si falla la búsqueda, se continúa con el form vacío
    }

    // Ocultar paso lookup, mostrar resto del formulario
    document.getElementById('politicasBox').classList.add('d-none');
    document.getElementById('cedulaContinuarBar').classList.add('d-none');
    document.getElementById('visitanteFields').classList.remove('d-none');
    document.getElementById('photoSection').classList.remove('d-none');
    document.getElementById('btnSubmit').classList.remove('d-none');
    visitanteLookupOk = true;

    // Restaurar botón (por si se vuelve a usar)
    document.getElementById('btnContinuarText').classList.remove('d-none');
    document.getElementById('btnContinuarSpinner').classList.add('d-none');
    document.getElementById('btnContinuar').disabled = false;

    // Sin autofocus — el usuario navega manualmente por los campos
    checkReady();
}

// ── Paso 2 ────────────────────────────────────────────────────────────────────
function setTipo(tipo) {
    tipoSeleccionado = tipo;
    const esEntrada  = tipo === 'entrada';

    document.getElementById('btnEntrada').className = esEntrada
        ? 'btn btn-success tipo-btn active'
        : 'btn btn-outline-success tipo-btn';
    document.getElementById('btnSalida').className = !esEntrada
        ? 'btn btn-danger tipo-btn active'
        : 'btn btn-outline-danger tipo-btn';

    // Ocultar botones Entrada/Salida tras seleccionar
    document.getElementById('tipoBtns').classList.add('d-none');

    // Mostrar form para ambos tipos
    document.getElementById('formBody').classList.remove('d-none');

    // Resetear foto al cambiar tipo
    fotoBase64 = null;
    visitanteLookupOk = false;
    document.getElementById('photoInput').value = '';
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('photoLabelText').textContent = 'Toca para abrir la cámara';

    document.getElementById('photoLabel').innerHTML = esEntrada
        ? 'Foto <span class="text-danger">*</span>'
        : 'Foto <span class="text-muted small">(opcional)</span>';

    if (tipoUsuario === 'visitante' && esEntrada) {
        // Paso 1 del visitante: solo cédula + políticas + Continuar
        document.getElementById('politicasBox').classList.remove('d-none');
        document.getElementById('chkPoliticas').checked = false;
        document.getElementById('politicasError').style.display = 'none';
        document.getElementById('cedulaContinuarBar').classList.remove('d-none');
        document.getElementById('visitanteFields').classList.add('d-none');
        document.getElementById('photoSection').classList.add('d-none');
        document.getElementById('btnSubmit').classList.add('d-none');
    } else {
        // Empleado o visitante salida: formulario completo directo
        document.getElementById('politicasBox').classList.add('d-none');
        document.getElementById('cedulaContinuarBar').classList.add('d-none');
        document.getElementById('visitanteFields').classList.add('d-none');
        document.getElementById('photoSection').classList.remove('d-none');
        document.getElementById('btnSubmit').classList.remove('d-none');
    }

    checkReady();
}

document.getElementById('photoInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        fotoBase64 = e.target.result;
        const preview = document.getElementById('photoPreview');
        preview.src = fotoBase64;
        preview.style.display = 'block';
        document.getElementById('photoLabelText').textContent = 'Foto tomada ✓ (toca para cambiar)';
        document.getElementById('photoError').textContent = '';
        checkReady();
    };
    reader.readAsDataURL(file);
});

['cedula','nombre','telefono','eps','arl','empresa','personaVisita','placa'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', () => {
        const pos = el.selectionStart;
        el.value = el.value.toUpperCase();
        try { el.setSelectionRange(pos, pos); } catch (_) {}
        checkReady();
    });
});
document.getElementById('chkPoliticas').addEventListener('change', checkReady);

function checkReady() {
    const cedula = document.getElementById('cedula').value.trim();
    const fotoOpcional = tipoSeleccionado === 'salida';
    let ok = tipoSeleccionado && cedula.length >= 5 && (fotoOpcional || fotoBase64);

    if (tipoUsuario === 'visitante' && tipoSeleccionado === 'entrada') {
        const aceptoPoliticas = document.getElementById('chkPoliticas').checked;
        if (!visitanteLookupOk) {
            // Aún en la pantalla de cédula: el Continuar se habilita con cédula + checkbox
            document.getElementById('btnContinuar').disabled = !(cedula.length >= 5 && aceptoPoliticas);
            document.getElementById('btnSubmit').disabled = true;
            return;
        }
        const nombre        = document.getElementById('nombre').value.trim();
        const telefono      = document.getElementById('telefono').value.trim();
        const eps           = document.getElementById('eps').value.trim();
        const arl           = document.getElementById('arl').value.trim();
        const empresa       = document.getElementById('empresa').value.trim();
        const personaVisita = document.getElementById('personaVisita').value.trim();
        ok = ok && nombre.length >= 2 && telefono.length >= 7
                && eps.length >= 2 && arl.length >= 2
                && empresa.length >= 2 && personaVisita.length >= 2;
    }

    document.getElementById('btnSubmit').disabled = !ok;
}

// ── GPS — inicia en background al cargar la página ───────────────────────────
const GPS_MIN_ACC  = 30;   // m — precisión suficiente para parar
const GPS_ACC_CAP  = 250;  // m — máximo margen que enviamos al server
const GPS_FALLBACK = 30000; // ms — espera máxima al hacer submit si aún no hay lectura

let gpsReady   = null;  // mejor lectura disponible (se actualiza en background)
let gpsWatchId = null;
let gpsError   = null;

function iniciarGPS() {
    if (!navigator.geolocation) return;
    gpsWatchId = navigator.geolocation.watchPosition(
        pos => {
            if (!gpsReady || pos.coords.accuracy < gpsReady.coords.accuracy) {
                gpsReady = pos;
            }
        },
        err => {
            const msgs = {
                1: 'Debes permitir el acceso a tu ubicación para registrar asistencia.',
                2: 'No se pudo obtener tu ubicación. Verifica el GPS activo.',
                3: 'Tiempo de espera agotado. Asegúrate de tener GPS activo.',
            };
            gpsError = msgs[err.code] || 'Error al obtener ubicación.';
        },
        { enableHighAccuracy: true, timeout: 60000, maximumAge: 30000 }
    );
}

function posToObj(pos) {
    return {
        lat:      pos.coords.latitude,
        lng:      pos.coords.longitude,
        accuracy: Math.min(Math.round(pos.coords.accuracy), GPS_ACC_CAP),
    };
}

function fallbackGetPosition() {
    return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(
            pos => resolve(posToObj(pos)),
            err => reject(new Error('No se pudo obtener tu ubicación. Verifica que el GPS esté activo.')),
            { enableHighAccuracy: false, timeout: 20000, maximumAge: 60000 }
        );
    });
}

function obtenerGPS() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Tu navegador no soporta geolocalización.'));
            return;
        }
        // Si ya tenemos una lectura disponible, la usamos de inmediato
        if (gpsReady) {
            resolve(posToObj(gpsReady));
            return;
        }
        // Sin lectura aún — esperamos hasta GPS_FALLBACK ms, luego fallback
        const start = Date.now();
        const poll = setInterval(() => {
            if (gpsReady) {
                clearInterval(poll);
                resolve(posToObj(gpsReady));
            } else if (Date.now() - start > GPS_FALLBACK) {
                clearInterval(poll);
                // Intentar con menor precisión antes de rechazar
                fallbackGetPosition().then(resolve).catch(reject);
            }
        }, 200);
    });
}

// Inicia GPS al cargar la página para tener lectura lista antes del submit
iniciarGPS();

// ── Submit ────────────────────────────────────────────────────────────────────
async function submitForm() {
    const cedula = document.getElementById('cedula').value.trim();
    let ok = true;

    if (!tipoSeleccionado) { alert('Selecciona Entrada o Salida.'); return; }

    if (cedula.length < 5) {
        document.getElementById('cedula').classList.add('is-invalid');
        document.getElementById('cedulaError').textContent = 'Ingresa un número de cédula válido.';
        ok = false;
    } else {
        document.getElementById('cedula').classList.remove('is-invalid');
    }

    const fotoOpcional = tipoSeleccionado === 'salida'; // opcional en salida para ambos tipos
    if (!fotoBase64 && !fotoOpcional) {
        document.getElementById('photoError').textContent = 'La foto es obligatoria.';
        ok = false;
    }

    const esVisitanteEntrada = tipoUsuario === 'visitante' && tipoSeleccionado === 'entrada';
    let nombre = '', personaVisita = '';

    if (esVisitanteEntrada) {
        const validarCampo = (id, errId, msg, minLen = 2) => {
            const val = document.getElementById(id).value.trim();
            if (val.length < minLen) {
                document.getElementById(id).classList.add('is-invalid');
                document.getElementById(errId).textContent = msg;
                ok = false;
                return '';
            }
            document.getElementById(id).classList.remove('is-invalid');
            return val;
        };

        nombre        = validarCampo('nombre',       'nombreError',       'El nombre es obligatorio.');
        validarCampo('telefono',     'telefonoError',   'El teléfono es obligatorio.', 7);
        validarCampo('eps',          'epsError',         'La EPS es obligatoria.');
        validarCampo('arl',          'arlError',         'La ARL es obligatoria.');
        validarCampo('empresa',      'empresaError',     'La empresa es obligatoria.');
        personaVisita = validarCampo('personaVisita', 'personaVisitaError', 'Indica a quién visitas.');
    }

    if (!ok) return;

    document.getElementById('btnText').classList.add('d-none');
    document.getElementById('btnSpinner').classList.remove('d-none');
    document.getElementById('btnSubmit').disabled = true;

    // ── Obtener GPS ───────────────────────────────────────────────────────────
    try {
        const pos = await obtenerGPS();
        userLat = pos.lat;
        userLng = pos.lng;
    } catch (gpsErr) {
        document.getElementById('btnText').classList.remove('d-none');
        document.getElementById('btnSpinner').classList.add('d-none');
        document.getElementById('btnSubmit').disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Ubicación requerida',
            text: gpsErr.message,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#4F46E5',
        });
        return;
    }

    const payload = {
        tipo_usuario:   tipoUsuario,
        cedula:         cedula,
        tipo:           tipoSeleccionado,
        foto_evidencia: fotoBase64,
        lat:            userLat,
        lng:            userLng,
    };

    if (esVisitanteEntrada) {
        payload.nombre         = nombre;
        payload.telefono       = document.getElementById('telefono').value.trim() || null;
        payload.eps            = document.getElementById('eps').value.trim() || null;
        payload.arl            = document.getElementById('arl').value.trim() || null;
        payload.empresa        = document.getElementById('empresa').value.trim() || null;
        payload.placa          = document.getElementById('placa').value.trim().toUpperCase() || null;
        payload.persona_visita = personaVisita;
    }

    try {
        const res  = await fetch(URL_POST, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        const box  = document.getElementById('resultBox');
        box.style.display = 'block';

        if (data.success) {
            box.className = 'alert alert-success py-3 mb-3';
            const tipoLabel = tipoSeleccionado === 'entrada' ? 'Entrada' : 'Salida';
            const quien = data.nombre
                ? `<strong>${data.nombre}</strong>`
                : (tipoUsuario === 'visitante' ? 'Visitante' : '');
            box.innerHTML = `
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                    <div>
                        <strong>${data.message}</strong><br>
                        <small>${quien} — ${tipoLabel}</small>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-success w-100" style="font-weight:700;border-radius:12px;padding:14px;" onclick="nuevoRegistro()">
                    <i class="fa-solid fa-rotate-left me-1"></i> Hacer otro registro
                </button>`;
            document.getElementById('step2').style.display = 'none';
        } else {
            document.getElementById('btnText').classList.remove('d-none');
            document.getElementById('btnSpinner').classList.add('d-none');
            document.getElementById('btnSubmit').disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'No se pudo registrar',
                text: data.message || 'Error al registrar.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#4F46E5',
            });
        }
    } catch (e) {
        document.getElementById('btnText').classList.remove('d-none');
        document.getElementById('btnSpinner').classList.add('d-none');
        document.getElementById('btnSubmit').disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'Verifica tu conexión e intenta de nuevo.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#4F46E5',
        });
    }
}
</script>
</body>
</html>
