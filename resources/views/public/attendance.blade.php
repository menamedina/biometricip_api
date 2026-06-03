<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Asistencia — {{ $sede->nombre }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            background: #f0f4ff;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 0;
            margin: 0;
        }
        .card-form {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            border-radius: 0;
            box-shadow: none;
            border: none;
        }
        @media (min-width: 520px) {
            body { padding: 24px 16px 40px; align-items: flex-start; }
            .card-form { min-height: unset; border-radius: 18px; box-shadow: 0 8px 32px rgba(79,70,229,.13); }
        }
        .sede-header {
            background: linear-gradient(135deg, #4F46E5, #7C3AED);
            border-radius: 0;
            padding: 28px 20px 22px;
            color: #fff;
        }
        @media (min-width: 520px) {
            .sede-header { border-radius: 18px 18px 0 0; }
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
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-outline-primary user-type-btn" onclick="selectTipoUsuario('empleado')">
                    <i class="fa-solid fa-id-badge"></i>Empleado
                </button>
                <button type="button" class="btn btn-outline-secondary user-type-btn" onclick="selectTipoUsuario('visitante')">
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
            <div class="d-flex gap-2 mb-4">
                <button type="button" class="btn btn-outline-success tipo-btn" id="btnEntrada" onclick="setTipo('entrada')">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Entrada
                </button>
                <button type="button" class="btn btn-outline-danger tipo-btn" id="btnSalida" onclick="setTipo('salida')">
                    <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Salida
                </button>
            </div>

            {{-- Cuerpo del formulario — se muestra tras elegir tipo --}}
            <div id="formBody" style="display:none;">

                {{-- Campos extra: solo visitante entrada --}}
                <div id="visitanteFields" style="display:none;">
                    <div class="section-divider">Datos del visitante</div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" id="nombre" class="form-control" placeholder="Ej: Juan Pérez" autocomplete="off">
                        <div class="invalid-feedback" id="nombreError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="tel" id="telefono" class="form-control" placeholder="Ej: 3001234567" inputmode="numeric" autocomplete="off">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">EPS</label>
                            <input type="text" id="eps" class="form-control" placeholder="Ej: Sura" autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">ARL</label>
                            <input type="text" id="arl" class="form-control" placeholder="Ej: Positiva" autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">¿A quién visita? <span class="text-danger">*</span></label>
                        <input type="text" id="personaVisita" class="form-control" placeholder="Nombre del empleado o área" autocomplete="off">
                        <div class="invalid-feedback" id="personaVisitaError"></div>
                    </div>

                    <div class="section-divider"></div>
                </div>

                {{-- Cédula --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Número de cédula <span class="text-danger">*</span></label>
                    <input type="number" id="cedula" class="form-control form-control-lg"
                           placeholder="Ej: 1234567890" inputmode="numeric" autocomplete="off">
                    <div class="invalid-feedback" id="cedulaError"></div>
                </div>

                {{-- Foto --}}
                <div class="mb-4" id="photoSection">
                    <label class="form-label fw-semibold">Foto <span class="text-danger">*</span></label>
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
const URL_POST = '{{ route("public.attendance.store", [$webToken, $sedeCode, $token]) }}';

let tipoUsuario      = null;
let tipoSeleccionado = null;
let fotoBase64       = null;

// ── Paso 1 ────────────────────────────────────────────────────────────────────
function selectTipoUsuario(tipo) {
    tipoUsuario = tipo;
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    document.getElementById('resultBox').style.display = 'none';
    document.getElementById('step2Label').textContent = tipo === 'empleado' ? 'Soy Empleado' : 'Soy Visitante';

    resetPaso2();
}

function volverPaso1() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
    document.getElementById('resultBox').style.display = 'none';
}

// ── Paso 2 ────────────────────────────────────────────────────────────────────
function resetPaso2() {
    tipoSeleccionado = null;
    fotoBase64       = null;
    ['nombre','cedula','telefono','eps','arl','personaVisita'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.value = ''; el.classList.remove('is-invalid'); }
    });
    ['nombreError','cedulaError','personaVisitaError'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '';
    });
    document.getElementById('photoLabelText').textContent = 'Toca para abrir la cámara';
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('photoError').textContent = '';
    document.getElementById('visitanteFields').style.display = 'none';

    // Para empleado el formulario se muestra directo; para visitante espera a elegir tipo
    document.getElementById('formBody').style.display = tipoUsuario === 'empleado' ? 'block' : 'none';

    ['btnEntrada','btnSalida'].forEach(id => {
        const btn = document.getElementById(id);
        btn.classList.remove('active','btn-success','btn-danger');
        btn.classList.add(id === 'btnEntrada' ? 'btn-outline-success' : 'btn-outline-danger');
    });
}

function setTipo(tipo) {
    tipoSeleccionado = tipo;
    const esEntrada  = tipo === 'entrada';

    document.getElementById('btnEntrada').classList.toggle('active', esEntrada);
    document.getElementById('btnEntrada').classList.toggle('btn-success', esEntrada);
    document.getElementById('btnEntrada').classList.toggle('btn-outline-success', !esEntrada);
    document.getElementById('btnSalida').classList.toggle('active', !esEntrada);
    document.getElementById('btnSalida').classList.toggle('btn-danger', !esEntrada);
    document.getElementById('btnSalida').classList.toggle('btn-outline-danger', esEntrada);

    if (tipoUsuario === 'visitante') {
        // Mostrar el formulario solo después de elegir el tipo
        document.getElementById('formBody').style.display = 'block';
        // Campos extra solo en entrada
        document.getElementById('visitanteFields').style.display = esEntrada ? 'block' : 'none';
        // Foto solo en entrada
        document.getElementById('photoSection').style.display = esEntrada ? 'block' : 'none';
        if (!esEntrada) { fotoBase64 = null; }
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

['cedula','nombre','personaVisita'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', checkReady);
});

function checkReady() {
    const cedula = document.getElementById('cedula').value.trim();
    const fotoOpcional = tipoUsuario === 'visitante' && tipoSeleccionado === 'salida';
    let ok = tipoSeleccionado && cedula.length >= 5 && (fotoOpcional || fotoBase64);

    // Campos extra para visitante entrada
    if (tipoUsuario === 'visitante' && tipoSeleccionado === 'entrada') {
        const nombre        = document.getElementById('nombre').value.trim();
        const personaVisita = document.getElementById('personaVisita').value.trim();
        ok = ok && nombre.length >= 2 && personaVisita.length >= 2;
    }

    document.getElementById('btnSubmit').disabled = !ok;
}

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

    const fotoOpcional = tipoUsuario === 'visitante' && tipoSeleccionado === 'salida';
    if (!fotoBase64 && !fotoOpcional) {
        document.getElementById('photoError').textContent = 'La foto es obligatoria.';
        ok = false;
    }

    const esVisitanteEntrada = tipoUsuario === 'visitante' && tipoSeleccionado === 'entrada';
    let nombre = '', personaVisita = '';

    if (esVisitanteEntrada) {
        nombre        = document.getElementById('nombre').value.trim();
        personaVisita = document.getElementById('personaVisita').value.trim();

        if (nombre.length < 2) {
            document.getElementById('nombre').classList.add('is-invalid');
            document.getElementById('nombreError').textContent = 'El nombre es obligatorio.';
            ok = false;
        } else {
            document.getElementById('nombre').classList.remove('is-invalid');
        }
        if (personaVisita.length < 2) {
            document.getElementById('personaVisita').classList.add('is-invalid');
            document.getElementById('personaVisitaError').textContent = 'Indica a quién visitas.';
            ok = false;
        } else {
            document.getElementById('personaVisita').classList.remove('is-invalid');
        }
    }

    if (!ok) return;

    document.getElementById('btnText').classList.add('d-none');
    document.getElementById('btnSpinner').classList.remove('d-none');
    document.getElementById('btnSubmit').disabled = true;

    const payload = {
        tipo_usuario:   tipoUsuario,
        cedula:         cedula,
        tipo:           tipoSeleccionado,
        foto_evidencia: fotoBase64,
    };

    if (esVisitanteEntrada) {
        payload.nombre         = nombre;
        payload.telefono       = document.getElementById('telefono').value.trim() || null;
        payload.eps            = document.getElementById('eps').value.trim() || null;
        payload.arl            = document.getElementById('arl').value.trim() || null;
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
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                    <div>
                        <strong>${data.message}</strong><br>
                        <small>${quien} — ${tipoLabel}</small>
                    </div>
                </div>`;
            document.getElementById('step2').style.display = 'none';
        } else {
            box.className = 'alert alert-danger py-3 mb-3';
            box.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i>${data.message || 'Error al registrar.'}`;
            document.getElementById('btnText').classList.remove('d-none');
            document.getElementById('btnSpinner').classList.add('d-none');
            document.getElementById('btnSubmit').disabled = false;
        }
    } catch (e) {
        const box = document.getElementById('resultBox');
        box.style.display = 'block';
        box.className = 'alert alert-danger py-3 mb-3';
        box.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-2"></i>Error de conexión. Intenta de nuevo.';
        document.getElementById('btnText').classList.remove('d-none');
        document.getElementById('btnSpinner').classList.add('d-none');
        document.getElementById('btnSubmit').disabled = false;
    }
}
</script>
</body>
</html>
