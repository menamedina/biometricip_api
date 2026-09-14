<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Doble Registro — {{ $sede->nombre }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }
        body {
            background: #fff url('{{ asset('img/Fondo_QR.png') }}') center top / contain no-repeat;
        }
        .sede-header {
            background: linear-gradient(135deg, #d97706, #f59e0b);
            padding: 28px 20px 22px;
            color: #fff;
        }
        .sede-header h5 { font-size: 1rem; opacity: .85; margin-bottom: 4px; }
        .sede-header h3 { font-size: 1.5rem; font-weight: 700; margin: 0; }
        .form-control-lg { font-size: 1.2rem; padding: 14px 16px; border-radius: 12px; }
        .btn-registrar {
            font-size: 1.2rem; font-weight: 700; border-radius: 14px;
            padding: 16px; touch-action: manipulation;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            border: none; color: #fff; width: 100%;
        }
        .btn-registrar:disabled { opacity: .6; }
        #photoPreview {
            width: 100%; max-height: 260px; border-radius: 12px;
            object-fit: cover; display: none; margin-top: 12px;
            border: 2px solid #f59e0b;
        }
        .photo-label {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border: 2px dashed #fcd34d; border-radius: 14px; padding: 28px 12px;
            cursor: pointer; color: #d97706; background: #fffbeb;
            transition: background .2s; touch-action: manipulation; min-height: 110px;
        }
        .photo-label:active { background: #fef3c7; }
        .photo-label i { font-size: 2.6rem; margin-bottom: 8px; }
        .photo-label span { font-size: 1rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="sede-header text-center">
    <h5><i class="fa-solid fa-right-left me-1"></i> Doble Registro</h5>
    <h3>{{ $sede->nombre }}</h3>
</div>

<div class="px-3 pt-4 pb-5" style="max-width:480px;margin:0 auto;">
    <p class="text-muted text-center mb-4" style="font-size:.95rem;">
        Al registrarte aquí se marcará automáticamente tu <strong>salida del lugar anterior</strong> y tu <strong>entrada a {{ $sede->nombre }}</strong>.
    </p>

    <div class="mb-3">
        <label class="form-label fw-semibold">Número de cédula</label>
        <input type="number" id="cedula" class="form-control form-control-lg" placeholder="Ej: 1234567890" inputmode="numeric">
    </div>

    <div class="mb-4">
        <label class="photo-label w-100" for="fotoInput">
            <i class="fa-solid fa-camera"></i>
            <span>Tomar foto (opcional)</span>
        </label>
        <input type="file" id="fotoInput" accept="image/*" capture="environment" class="d-none">
        <img id="photoPreview" src="" alt="Vista previa">
    </div>

    <button class="btn-registrar" id="btnRegistrar" onclick="registrar()">
        <i class="fa-solid fa-right-left me-2"></i> Registrar
    </button>

    <div id="mensajeError" class="alert alert-danger mt-3 d-none"></div>
</div>

<script>
let fotoBase64 = null;
let userLat = null, userLng = null, userAccuracy = null;

// Obtener GPS al cargar
navigator.geolocation?.getCurrentPosition(
    pos => { userLat = pos.coords.latitude; userLng = pos.coords.longitude; userAccuracy = Math.round(pos.coords.accuracy); },
    () => {},
    { enableHighAccuracy: true, timeout: 10000 }
);

// Preview foto
document.getElementById('fotoInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        fotoBase64 = e.target.result;
        const img = document.getElementById('photoPreview');
        img.src = fotoBase64;
        img.style.display = 'block';
        document.querySelector('.photo-label span').textContent = 'Foto tomada ✓';
    };
    reader.readAsDataURL(file);
});

async function registrar() {
    const cedula = document.getElementById('cedula').value.trim();
    if (!cedula) { mostrarError('Ingresa tu número de cédula.'); return; }
    if (!userLat || !userLng) {
        mostrarError('No se pudo obtener tu ubicación GPS. Permite el acceso a la ubicación e intenta de nuevo.');
        return;
    }

    const btn = document.getElementById('btnRegistrar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Registrando...';
    ocultarError();

    try {
        const res = await fetch('{{ route('public.attendance.doble.store', [$webToken, $sedeCode, $token]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                cedula,
                foto_evidencia: fotoBase64 || null,
                lat: userLat,
                lng: userLng,
                accuracy: userAccuracy,
            }),
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: '¡Listo!',
                text: data.message,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#d97706',
            });
            document.getElementById('cedula').value = '';
            fotoBase64 = null;
            document.getElementById('photoPreview').style.display = 'none';
            document.querySelector('.photo-label span').textContent = 'Tomar foto (opcional)';
        } else {
            mostrarError(data.message || 'Error al registrar.');
        }
    } catch(e) {
        mostrarError('Error de conexión. Intenta de nuevo.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-right-left me-2"></i> Registrar';
    }
}

function mostrarError(msg) {
    const el = document.getElementById('mensajeError');
    el.textContent = msg;
    el.classList.remove('d-none');
}
function ocultarError() {
    document.getElementById('mensajeError').classList.add('d-none');
}
</script>
</body>
</html>
