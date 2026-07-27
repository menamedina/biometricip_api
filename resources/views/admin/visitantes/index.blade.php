@extends('layouts.admin')
@section('title', 'Visitantes')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-user-check me-2 text-primary"></i>Visitantes</h4>
                    <p class="text-muted mb-0">Registro de visitas por sede</p>
                </div>
                <button class="btn btn-primary" onclick="abrirRegistroManual()">
                    <i class="ti ti-plus me-1"></i> Registrar entrada
                </button>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label form-label-sm mb-1">Sede</label>
                            <select class="form-select form-select-sm" id="filterSede" onchange="loadVisitantes()">
                                <option value="">Todas las sedes</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="filterDesde" onchange="loadVisitantes()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="filterHasta" onchange="loadVisitantes()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm mb-1">Cédula / Nombre</label>
                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Buscar..." oninput="loadVisitantes()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Estado</label>
                            <select class="form-select form-select-sm" id="filterEstado" onchange="loadVisitantes()">
                                <option value="">Todos</option>
                                <option value="en_sede">En sede</option>
                                <option value="salieron">Con salida</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-sm btn-secondary flex-fill mt-3" onclick="clearFilters()">
                                <i class="ti ti-x me-1"></i> Limpiar
                            </button>
                            <button class="btn btn-sm btn-success flex-fill mt-3" onclick="exportarExcel()">
                                <i class="ti ti-file-spreadsheet me-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Visita a</th>
                                    <th>Sede</th>
                                    <th>EPS / ARL</th>
                                    <th>Teléfono</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Tiempo en sede</th>
                                    <th>Foto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="visitantesTbody">
                                <tr><td colspan="9" class="text-center text-muted py-3">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Registro Manual --}}
<div class="modal fade" id="modalRegistroManual" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-user-plus me-2"></i>Registrar entrada manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="registroManualAlert" class="d-none mb-3"></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Sede <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="rm_sede_id">
                            <option value="">Selecciona una sede...</option>
                            @foreach($sedes as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Fecha y hora de entrada</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="rm_hora_entrada">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Cédula <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="rm_cedula" placeholder="Ej: 1234567890" inputmode="numeric">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="rm_nombre" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="rm_telefono" placeholder="Ej: 3001234567" inputmode="numeric">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Empresa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="rm_empresa" placeholder="Ej: Servicios ABC S.A.S">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">EPS <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="rm_eps" placeholder="Ej: Sura">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">ARL <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="rm_arl" placeholder="Ej: Positiva">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Placa del vehículo</label>
                        <input type="text" class="form-control form-control-sm" id="rm_placa" placeholder="Ej: ABC-123" style="text-transform:uppercase;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">¿A quién visita? <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="rm_persona_visita" placeholder="Nombre del empleado o área">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarVisitante" onclick="guardarVisitanteManual()">
                    <i class="ti ti-check me-1"></i> Registrar entrada
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal foto --}}
<div class="modal fade" id="fotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Foto del visitante</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="fotoSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="fotoContent" class="d-none">
                    <div class="row g-3">
                        <div class="col-6 text-center">
                            <p class="fw-semibold mb-2 small text-success"><i class="ti ti-arrow-right-to-arc me-1"></i>Entrada</p>
                            <img id="fotoEntrada" src="" alt="Entrada" class="rounded w-100" style="height:280px;object-fit:cover;">
                            <p id="fotoEntradaVacio" class="text-muted small mt-2 d-none">Sin foto</p>
                        </div>
                        <div class="col-6 text-center">
                            <p class="fw-semibold mb-2 small text-danger"><i class="ti ti-arrow-right-from-arc me-1"></i>Salida</p>
                            <img id="fotoSalida" src="" alt="Salida" class="rounded w-100" style="height:280px;object-fit:cover;">
                            <p id="fotoSalidaVacio" class="text-muted small mt-2 d-none">Sin foto</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
const sedesData = @json($sedes);
let dataLoadedAt = null;

// Fecha por defecto: hoy en hora local
function localDateStr() {
    const d = new Date();
    return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}
document.getElementById('filterDesde').value = localDateStr();
document.getElementById('filterHasta').value = localDateStr();

function loadSedes() {
    const sel = document.getElementById('filterSede');
    sedesData.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id; o.textContent = s.nombre;
        sel.appendChild(o);
    });
}

async function loadVisitantes() {
    const params = new URLSearchParams({
        sede_id:  document.getElementById('filterSede').value,
        desde:    document.getElementById('filterDesde').value,
        hasta:    document.getElementById('filterHasta').value,
        search:   document.getElementById('filterSearch').value,
        estado:   document.getElementById('filterEstado').value,
    });

    const res  = await fetch(`/admin/visitantes/list?${params}`);
    const data = await res.json();
    const tbody = document.getElementById('visitantesTbody');

    if (!data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Sin registros</td></tr>';
        return;
    }

    dataLoadedAt = new Date();
    tbody.innerHTML = data.data.map(v => `
        <tr>
            <td><strong>${v.nombre ?? '—'}</strong></td>
            <td>${v.cedula}</td>
            <td>${v.persona_visita ?? '—'}</td>
            <td><span class="badge bg-primary">${v.sede?.nombre ?? '—'}</span></td>
            <td><small>${v.eps ?? '—'} / ${v.arl ?? '—'}</small></td>
            <td>${v.telefono ?? '—'}</td>
            <td><small>${formatDT(v.hora_entrada)}</small></td>
            <td><small>${v.hora_salida ? formatDT(v.hora_salida) : '<span class="badge bg-warning text-dark">En sede</span>'}</small></td>
            <td><small>${tiempoEnSede(v)}</small></td>
            <td>${v.imagen_entrada ? `<button class="btn btn-sm btn-outline-primary" onclick="verFoto(${v.id})"><i class="ti ti-photo"></i></button>` : '—'}</td>
            <td>${botonForzarSalida(v)}</td>
        </tr>
    `).join('');
}

function parseDate(dt) {
    if (!dt) return null;
    // Laravel serializa campos datetime con offset (-05:00); parsear directamente
    return new Date(dt);
}

function formatMins(mins) {
    if (mins < 1) return '< 1m';
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

function tiempoEnSede(v) {
    if (v.minutos_en_sede == null) return '—';
    if (v.hora_salida) return formatMins(v.minutos_en_sede);
    // Activo: base del servidor + segundos transcurridos desde que cargó la página
    return `<span class="text-warning fw-semibold" data-minutos="${v.minutos_en_sede}">⏳ ${formatMins(v.minutos_en_sede)}</span>`;
}

function botonForzarSalida(v) {
    if (v.hora_salida) return '';
    return `<button class="btn btn-sm btn-outline-danger" onclick="forzarSalida(${v.id})" title="Registrar salida">
        <i class="ti ti-door-exit"></i> Salida
    </button>`;
}

async function forzarSalida(id) {
    const result = await Swal.fire({
        title: '¿Registrar salida?',
        text: 'Se registrará la hora de salida de este visitante.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4F46E5',
        cancelButtonColor: '#6c757d',
    });
    if (!result.isConfirmed) return;
    await fetch(`/admin/visitantes/${id}/forzar-salida`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });
    loadVisitantes();
}

function formatDT(dt) {
    if (!dt) return '—';
    const d = parseDate(dt);
    const dd   = String(d.getDate()).padStart(2, '0');
    const mm   = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = d.getFullYear();
    const hh   = String(d.getHours()).padStart(2, '0');
    const min  = String(d.getMinutes()).padStart(2, '0');
    return `${dd}/${mm}/${yyyy} ${hh}:${min}`;
}

async function verFoto(visitanteId) {
    const spinner = document.getElementById('fotoSpinner');
    const content = document.getElementById('fotoContent');
    spinner.classList.remove('d-none');
    content.classList.add('d-none');
    new bootstrap.Modal(document.getElementById('fotoModal')).show();

    const res  = await fetch(`/admin/visitantes/${visitanteId}/foto`);
    const data = await res.json();

    const imgE = document.getElementById('fotoEntrada');
    const imgS = document.getElementById('fotoSalida');
    const vacE = document.getElementById('fotoEntradaVacio');
    const vacS = document.getElementById('fotoSalidaVacio');

    if (data.entrada) { imgE.src = data.entrada; imgE.classList.remove('d-none'); vacE.classList.add('d-none'); }
    else              { imgE.classList.add('d-none'); vacE.classList.remove('d-none'); }

    if (data.salida)  { imgS.src = data.salida;  imgS.classList.remove('d-none'); vacS.classList.add('d-none'); }
    else              { imgS.classList.add('d-none'); vacS.classList.remove('d-none'); }

    spinner.classList.add('d-none');
    content.classList.remove('d-none');
}

function exportarExcel() {
    const params = new URLSearchParams({
        sede_id: document.getElementById('filterSede').value,
        desde:   document.getElementById('filterDesde').value,
        hasta:   document.getElementById('filterHasta').value,
        search:  document.getElementById('filterSearch').value,
        estado:  document.getElementById('filterEstado').value,
        export:  'xlsx',
    });
    window.location.href = `/admin/visitantes/list?${params}`;
}

function clearFilters() {
    document.getElementById('filterSede').value   = '';
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterDesde').value  = localDateStr();
    document.getElementById('filterHasta').value  = localDateStr();
    loadVisitantes();
}

// Actualiza en vivo los contadores de visitantes en sede cada 30 s
setInterval(() => {
    if (!dataLoadedAt) return;
    const elapsedMins = Math.floor((new Date() - dataLoadedAt) / 60000);
    document.querySelectorAll('[data-minutos]').forEach(el => {
        const base = parseInt(el.getAttribute('data-minutos'), 10);
        el.textContent = '⏳ ' + formatMins(base + elapsedMins);
    });
}, 30000);

document.addEventListener('DOMContentLoaded', () => { loadSedes(); loadVisitantes(); });

// ── Registro manual ──────────────────────────────────────────────────────────
function abrirRegistroManual() {
    // Resetear campos
    ['rm_sede_id','rm_cedula','rm_nombre','rm_telefono','rm_empresa',
     'rm_eps','rm_arl','rm_placa','rm_persona_visita'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    // Hora de entrada: ahora por defecto en hora local del navegador
    const now = new Date();
    now.setSeconds(0, 0);
    const localISO = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('rm_hora_entrada').value = localISO;
    document.getElementById('registroManualAlert').className = 'd-none mb-3';
    new bootstrap.Modal(document.getElementById('modalRegistroManual')).show();
}

async function guardarVisitanteManual() {
    const btn    = document.getElementById('btnGuardarVisitante');
    const alert  = document.getElementById('registroManualAlert');

    const payload = {
        sede_id:        document.getElementById('rm_sede_id').value,
        cedula:         document.getElementById('rm_cedula').value.trim(),
        nombre:         document.getElementById('rm_nombre').value.trim(),
        telefono:       document.getElementById('rm_telefono').value.trim(),
        empresa:        document.getElementById('rm_empresa').value.trim(),
        eps:            document.getElementById('rm_eps').value.trim(),
        arl:            document.getElementById('rm_arl').value.trim(),
        placa:          document.getElementById('rm_placa').value.trim().toUpperCase() || null,
        persona_visita: document.getElementById('rm_persona_visita').value.trim(),
        hora_entrada:   document.getElementById('rm_hora_entrada').value || null,
    };

    // Validación básica
    const requeridos = ['sede_id','cedula','nombre','telefono','empresa','eps','arl','persona_visita'];
    const faltante = requeridos.find(k => !payload[k]);
    if (faltante) {
        alert.className = 'alert alert-warning mb-3';
        alert.textContent = 'Completa todos los campos obligatorios.';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        const res  = await fetch('{{ route("admin.visitantes.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (res.ok && data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalRegistroManual')).hide();
            loadVisitantes();
        } else {
            alert.className = 'alert alert-danger mb-3';
            alert.textContent = data.message || 'Error al guardar.';
        }
    } catch (e) {
        alert.className = 'alert alert-danger mb-3';
        alert.textContent = 'Error de conexión.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Registrar entrada';
    }
}
</script>
@endpush
