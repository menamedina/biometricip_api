@extends('layouts.admin')
@section('title', 'API Tokens')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <h4 class="mb-1"><i class="fa-solid fa-code me-2 text-primary"></i>API Tokens</h4>
            <p class="text-muted mb-0">Integra BiometricIP con sistemas externos mediante tu token personal de API.</p>
        </div>
    </div>

    {{-- Token y scope --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-key me-2 text-warning"></i>Mi Token de API</h6>

                    @if($user->api_token)
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Token activo</label>
                        <div class="input-group">
                            <input type="password" id="tokenInput" class="form-control form-control-sm font-monospace"
                                   value="{{ $user->api_token }}" readonly>
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="toggleToken()" title="Mostrar / Ocultar">
                                <i class="fa-solid fa-eye" id="tokenEyeIcon"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="copiarToken()" title="Copiar al portapapeles">
                                <i class="fa-solid fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning small py-2 mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        No tienes un token generado aún. Haz clic en <strong>Generar Token</strong> para crear uno.
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Visibilidad de registros</label>
                        <select id="scopeSelect" class="form-select form-select-sm" style="max-width:220px;" onchange="actualizarScope(this.value)">
                            <option value="personal" {{ ($user->scope_registros ?? 'personal') === 'personal' ? 'selected' : '' }}>
                                Personal — solo mis registros
                            </option>
                            <option value="equipo" {{ ($user->scope_registros ?? 'personal') === 'equipo' ? 'selected' : '' }}>
                                Equipo — mis registros + equipo
                            </option>
                            <option value="empresa" {{ ($user->scope_registros ?? 'personal') === 'empresa' ? 'selected' : '' }}>
                                Empresa — todos los registros
                            </option>
                        </select>
                        <div class="form-text small">Aplica tanto al panel admin como a la API externa.</div>
                    </div>

                    <button class="btn btn-warning btn-sm" onclick="confirmarRegenerar()">
                        <i class="fa-solid fa-rotate me-1"></i>
                        {{ $user->api_token ? 'Regenerar Token' : 'Generar Token' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100 bg-dark text-white">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3 text-white"><i class="fa-solid fa-terminal me-2 text-success"></i>Autenticación</h6>
                    <p class="small text-secondary mb-2">Incluye el header en cada petición:</p>
                    <pre class="rounded p-2 mb-3 small" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto;">Authorization: Bearer <span id="tokenPreview" class="text-yellow-300">{{ $user->api_token ? substr($user->api_token, 0, 8) . '...' : '{tu_token}' }}</span></pre>
                    <p class="small text-secondary mb-2">Base URL:</p>
                    <pre class="rounded p-2 small" style="background:#1e1e2e;color:#a6e3a1;overflow-x:auto;">{{ rtrim(config('app.url'), '/') }}/api/external</pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Endpoints --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-plug me-2 text-info"></i>Endpoints disponibles</h6>

                    <div class="accordion" id="accordionEndpoints">

                        {{-- Endpoint: Attendance --}}
                        <div class="accordion-item border mb-2 rounded">
                            <h2 class="accordion-header">
                                <div class="d-flex align-items-center px-3 py-2 gap-2" style="cursor:pointer;"
                                     data-bs-toggle="collapse" data-bs-target="#ep1">
                                    <span class="badge bg-success" style="font-size:.75rem;">GET</span>
                                    <span class="font-monospace fw-semibold" style="user-select:text;font-size:.85rem;"
                                          onclick="event.stopPropagation()">/attendance</span>
                                    <span class="ms-auto text-muted fw-normal d-none d-md-inline" style="font-size:.8rem;">Registros de asistencia</span>
                                    <i class="ti ti-chevron-down text-muted ms-2" style="font-size:.85rem;"></i>
                                </div>
                            </h2>
                            <div id="ep1" class="accordion-collapse collapse" data-bs-parent="#accordionEndpoints">
                                <div class="accordion-body pt-2">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <p class="small fw-semibold mb-2">Parámetros opcionales</p>
                                            <table class="table table-sm table-bordered small mb-0">
                                                <thead class="table-light">
                                                    <tr><th>Param</th><th>Tipo</th><th>Descripción</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td><code>date_from</code></td><td>string</td><td>YYYY-MM-DD. Sin filtro si se omite</td></tr>
                                                    <tr><td><code>date_to</code></td><td>string</td><td>YYYY-MM-DD. Sin filtro si se omite</td></tr>
                                                    <tr><td><code>tipo</code></td><td>string</td><td>entrada | salida | salida_almuerzo | regreso_almuerzo</td></tr>
                                                    <tr><td><code>sede_id</code></td><td>int</td><td>Filtrar por sede</td></tr>
                                                    <tr><td><code>user_id</code></td><td>int</td><td>Filtrar por empleado (dentro del scope)</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small fw-semibold mb-2">Ejemplo de llamada</p>
                                            <pre class="rounded p-2 small mb-2" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto;">curl -X GET \
  "{{ rtrim(config('app.url'), '/') }}/api/external/attendance?date_from=2026-11-28&date_to=2026-11-28" \
  -H "Authorization: Bearer {{ $user->api_token ?? '{tu_token}' }}" \
  -H "Accept: application/json"</pre>
                                            <p class="small fw-semibold mb-2">Respuesta</p>
                                            <pre class="rounded p-2 small" style="background:#1e1e2e;color:#a6e3a1;overflow-x:auto;">[
  {
    "codigo_empleado": "ADM-0001",
    "cedula": "103062384",
    "nombre": "Usuario DEMO",
    "lider": null,
    "departamento": "Tecnología",
    "cargo": "Desarrollador Backend",
    "horario": "Turno Completo",
    "foto_marcacion_url": "http://.../api/external/attendance/photo?path=fotos%2F1%2F...",
    "sede": "Sede Principal",
    "tipo": "entrada",
    "fecha_hora": "2026-11-28 08:05",
    "metodo": "foto",
    "lat": "6.2442567",
    "lng": "-75.6131617",
    "maps_url": "https://www.google.com/maps?q=6.2442567,-75.6131617",
    "qr_validado": false,
    "distancia_oficina_mts": 16.03,
    "observacion": null
  }
]</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Endpoint: Employees --}}
                        <div class="accordion-item border mb-2 rounded">
                            <h2 class="accordion-header">
                                <div class="d-flex align-items-center px-3 py-2 gap-2" style="cursor:pointer;"
                                     data-bs-toggle="collapse" data-bs-target="#ep2">
                                    <span class="badge bg-success" style="font-size:.75rem;">GET</span>
                                    <span class="font-monospace fw-semibold" style="user-select:text;font-size:.85rem;"
                                          onclick="event.stopPropagation()">/employees</span>
                                    <span class="ms-auto text-muted fw-normal d-none d-md-inline" style="font-size:.8rem;">Lista de empleados</span>
                                    <i class="ti ti-chevron-down text-muted ms-2" style="font-size:.85rem;"></i>
                                </div>
                            </h2>
                            <div id="ep2" class="accordion-collapse collapse" data-bs-parent="#accordionEndpoints">
                                <div class="accordion-body pt-2">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <p class="small fw-semibold mb-2">Parámetros opcionales</p>
                                            <table class="table table-sm table-bordered small mb-0">
                                                <thead class="table-light">
                                                    <tr><th>Param</th><th>Tipo</th><th>Descripción</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td><code>search</code></td><td>string</td><td>Busca por nombre, cédula o código</td></tr>
                                                </tbody>
                                            </table>
                                            <div class="alert alert-info small py-2 mt-2 mb-0">
                                                <i class="fa-solid fa-circle-info me-1"></i>
                                                Los empleados devueltos dependen del <strong>scope</strong> configurado arriba.
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small fw-semibold mb-2">Ejemplo de llamada</p>
                                            <pre class="rounded p-2 small mb-2" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto;">curl -X GET \
  "{{ rtrim(config('app.url'), '/') }}/api/external/employees?search=Juan" \
  -H "Authorization: Bearer {{ $user->api_token ?? '{tu_token}' }}" \
  -H "Accept: application/json"</pre>
                                            <p class="small fw-semibold mb-2">Respuesta</p>
                                            <pre class="rounded p-2 small" style="background:#1e1e2e;color:#a6e3a1;overflow-x:auto;">[
  {
    "codigo_empleado": "ADM-0001",
    "cedula": "103062384",
    "nombre": "Usuario DEMO",
    "role": "admin",
    "lider": null,
    "departamento": "Tecnología",
    "cargo": "Desarrollador Backend",
    "horario": "Turno Completo",
    "empleador": "Empresa XYZ",
    "empresa": "Mi Empresa S.A.",
    "sedes": "Sede Principal, Sede Norte",
    "is_active": true
  }
]</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Endpoint: Summary --}}
                        <div class="accordion-item border mb-2 rounded">
                            <h2 class="accordion-header">
                                <div class="d-flex align-items-center px-3 py-2 gap-2" style="cursor:pointer;"
                                     data-bs-toggle="collapse" data-bs-target="#ep3">
                                    <span class="badge bg-success" style="font-size:.75rem;">GET</span>
                                    <span class="font-monospace fw-semibold" style="user-select:text;font-size:.85rem;"
                                          onclick="event.stopPropagation()">/summary</span>
                                    <span class="ms-auto text-muted fw-normal d-none d-md-inline" style="font-size:.8rem;">Resumen diario de asistencia</span>
                                    <i class="ti ti-chevron-down text-muted ms-2" style="font-size:.85rem;"></i>
                                </div>
                            </h2>
                            <div id="ep3" class="accordion-collapse collapse" data-bs-parent="#accordionEndpoints">
                                <div class="accordion-body pt-2">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <p class="small fw-semibold mb-2">Parámetros</p>
                                            <table class="table table-sm table-bordered small mb-0">
                                                <thead class="table-light">
                                                    <tr><th>Param</th><th>Tipo</th><th>Descripción</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td><code>date_from</code></td><td>string</td><td>YYYY-MM-DD (requerido)</td></tr>
                                                    <tr><td><code>date_to</code></td><td>string</td><td>YYYY-MM-DD (requerido)</td></tr>
                                                    <tr><td><code>user_id</code></td><td>int</td><td>Filtrar por empleado (opcional)</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small fw-semibold mb-2">Ejemplo de llamada</p>
                                            <pre class="rounded p-2 small mb-2" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto;">curl -X GET \
  "{{ rtrim(config('app.url'), '/') }}/api/external/summary?date_from=2026-11-27&date_to=2026-11-27" \
  -H "Authorization: Bearer {{ $user->api_token ?? '{tu_token}' }}" \
  -H "Accept: application/json"</pre>
                                            <p class="small fw-semibold mb-2">Respuesta</p>
                                            <pre class="rounded p-2 small" style="background:#1e1e2e;color:#a6e3a1;overflow-x:auto;">[
  {
    "codigo_empleado": "ADM-0001",
    "nombre": "Usuario DEMO",
    "horario": "Turno Completo",
    "sede": "Sede Principal-1",
    "fecha": "2026-11-27",
    "entrada_1": "11:23",
    "salida_1": "11:25",
    "entrada_2": "11:25",
    "salida_2": "11:27",
    "total_horas": "00:04"
  }
]</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Endpoint: Monthly --}}
                        <div class="accordion-item border mb-2 rounded">
                            <h2 class="accordion-header">
                                <div class="d-flex align-items-center px-3 py-2 gap-2" style="cursor:pointer;"
                                     data-bs-toggle="collapse" data-bs-target="#ep4">
                                    <span class="badge bg-success" style="font-size:.75rem;">GET</span>
                                    <span class="font-monospace fw-semibold" style="user-select:text;font-size:.85rem;"
                                          onclick="event.stopPropagation()">/monthly</span>
                                    <span class="ms-auto text-muted fw-normal d-none d-md-inline" style="font-size:.8rem;">Resumen mensual de horas</span>
                                    <i class="ti ti-chevron-down text-muted ms-2" style="font-size:.85rem;"></i>
                                </div>
                            </h2>
                            <div id="ep4" class="accordion-collapse collapse" data-bs-parent="#accordionEndpoints">
                                <div class="accordion-body pt-2">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <p class="small fw-semibold mb-2">Parámetros</p>
                                            <table class="table table-sm table-bordered small mb-0">
                                                <thead class="table-light">
                                                    <tr><th>Param</th><th>Tipo</th><th>Descripción</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td><code>anio</code></td><td>int</td><td>Año (requerido). Ej: 2026</td></tr>
                                                    <tr><td><code>mes</code></td><td>int</td><td>Mes 1-12 (requerido). Ej: 11</td></tr>
                                                    <tr><td><code>user_id</code></td><td>int</td><td>Filtrar por empleado (opcional)</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small fw-semibold mb-2">Ejemplo de llamada</p>
                                            <pre class="rounded p-2 small mb-2" style="background:#1e1e2e;color:#cdd6f4;overflow-x:auto;">curl -X GET \
  "{{ rtrim(config('app.url'), '/') }}/api/external/monthly?anio=2026&mes=11" \
  -H "Authorization: Bearer {{ $user->api_token ?? '{tu_token}' }}" \
  -H "Accept: application/json"</pre>
                                            <p class="small fw-semibold mb-2">Respuesta</p>
                                            <pre class="rounded p-2 small" style="background:#1e1e2e;color:#a6e3a1;overflow-x:auto;">[
  {
    "codigo_empleado": "ADM-0001",
    "nombre": "Usuario DEMO",
    "dia_1": null,
    "dia_2": null,
    "...": "...",
    "dia_18": "00:00",
    "dia_27": "00:02",
    "dia_28": null,
    "dia_30": null,
    "total": "00:02"
  }
]</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /accordion --}}

                    <div class="mt-3 alert alert-secondary small py-2 mb-0">
                        <i class="fa-solid fa-shield-halved me-1"></i>
                        Los endpoints respetan el <strong>scope de visibilidad</strong> de tu token: solo devuelven datos a los que tienes acceso desde el panel admin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal confirmar regenerar --}}
<div class="modal fade" id="modalRegenerarToken" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa-solid fa-rotate me-2 text-warning"></i>Regenerar Token</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                Al regenerar el token, el token actual dejará de funcionar inmediatamente.<br>
                Deberás actualizar todas las integraciones que lo usen.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm" onclick="regenerarToken()">Regenerar</button>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:2000;">
    <div id="toastApi" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastApiMsg"></div>
            <button type="button" class="btn-close btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

function toggleToken() {
    const input = document.getElementById('tokenInput');
    const icon  = document.getElementById('tokenEyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

function copiarToken() {
    const input = document.getElementById('tokenInput');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        mostrarToast('Token copiado al portapapeles.', 'bg-success text-white');
    });
}

function confirmarRegenerar() {
    new bootstrap.Modal(document.getElementById('modalRegenerarToken')).show();
}

async function regenerarToken() {
    bootstrap.Modal.getInstance(document.getElementById('modalRegenerarToken')).hide();
    try {
        const res = await fetch('{{ route('admin.api.regenerate') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        // Actualizar campo
        const input = document.getElementById('tokenInput');
        if (input) {
            input.value = data.api_token;
        } else {
            // Primera vez — recargar la página para mostrar el campo
            location.reload();
            return;
        }

        // Actualizar preview en el bloque de código
        const preview = document.getElementById('tokenPreview');
        if (preview) preview.textContent = data.api_token.substring(0, 8) + '...';

        mostrarToast('Token regenerado correctamente.', 'bg-success text-white');
    } catch(e) {
        mostrarToast('Error al regenerar el token: ' + e.message, 'bg-danger text-white');
    }
}

async function actualizarScope(value) {
    try {
        const res = await fetch('{{ route('admin.api.scope') }}', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ scope_registros: value }),
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        mostrarToast('Visibilidad actualizada.', 'bg-success text-white');
    } catch(e) {
        mostrarToast('Error al actualizar visibilidad: ' + e.message, 'bg-danger text-white');
    }
}

function mostrarToast(msg, clases) {
    const toast = document.getElementById('toastApi');
    document.getElementById('toastApiMsg').textContent = msg;
    toast.className = 'toast align-items-center border-0 ' + clases;
    new bootstrap.Toast(toast, { delay: 3000 }).show();
}
</script>
@endpush
