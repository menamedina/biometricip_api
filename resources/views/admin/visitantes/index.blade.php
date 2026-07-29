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
                <div class="card-header p-3 d-flex justify-content-between align-items-center border-bottom-0 pb-2">
                    <span class="text-muted small" id="totalLabel">Cargando...</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-field="cedula">Cédula</th>
                                    <th class="sortable" data-field="nombre">Nombre</th>
                                    <th class="sortable" data-field="empresa">Empresa</th>
                                    <th>Teléfono</th>
                                    <th>EPS / ARL</th>
                                    <th>Placa</th>
                                    <th class="sortable" data-field="persona_visita">Visita a</th>
                                    <th class="sortable" data-field="sede">Sede</th>
                                    <th class="sortable" data-field="hora_entrada">Entrada</th>
                                    <th class="sortable" data-field="hora_salida">Salida</th>
                                    <th class="sortable" data-field="minutos_en_sede">Tiempo en sede</th>
                                    <th>Inducción</th>
                                    <th>Última inducción</th>
                                    <th>Observación</th>
                                    <th>Foto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="visitantesTbody">
                                <tr><td colspan="16" class="text-center text-muted py-3">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-2 px-3" id="paginacionContainer" style="display:none!important">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted" id="paginacionInfo"></small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginacionLinks"></ul>
                        </nav>
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
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm rm-upper" id="rm_cedula" placeholder="Ej: 1234567890" inputmode="numeric">
                            <button class="btn btn-outline-secondary" type="button" id="btnBuscarCedula" onclick="buscarPorCedula()" title="Buscar último registro">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                        <div id="rm_cedula_msg" class="form-text d-none"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="rm_nombre" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="rm_telefono" placeholder="Ej: 3001234567" inputmode="numeric">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Empresa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="rm_empresa" placeholder="Ej: Servicios ABC S.A.S">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">EPS <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="rm_eps" placeholder="Ej: Sura">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">ARL <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="rm_arl" placeholder="Ej: Positiva">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Placa del vehículo</label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="rm_placa" placeholder="Ej: ABC-123">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">¿A quién visita? <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="rm_persona_visita" placeholder="Nombre del empleado o área">
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

@push('styles')
<style>
.rm-upper { text-transform: uppercase; }
.rm-upper::placeholder { text-transform: none; }
</style>
@endpush

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
const sedesData = @json($sedes);
let dataLoadedAt    = null;
let visitantesData  = [];
let sortField       = 'hora_entrada';
let sortDir         = 'desc';
let currentPage     = 1;
let paginaMeta      = {};

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

async function loadVisitantes(page = 1) {
    currentPage = page;
    const params = new URLSearchParams({
        sede_id:  document.getElementById('filterSede').value,
        desde:    document.getElementById('filterDesde').value,
        hasta:    document.getElementById('filterHasta').value,
        search:   document.getElementById('filterSearch').value,
        estado:   document.getElementById('filterEstado').value,
        page:     currentPage,
    });

    document.getElementById('totalLabel').textContent = 'Cargando...';

    const res  = await fetch(`/admin/visitantes/list?${params}`);
    const data = await res.json();
    const tbody = document.getElementById('visitantesTbody');

    if (!data.data || data.data.length === 0) {
        visitantesData = [];
        paginaMeta = {};
        tbody.innerHTML = '<tr><td colspan="16" class="text-center text-muted py-3">Sin registros</td></tr>';
        document.getElementById('totalLabel').textContent = '0 visitantes encontrados';
        document.getElementById('paginacionContainer').style.setProperty('display', 'none', 'important');
        return;
    }

    paginaMeta     = { total: data.total, per_page: data.per_page, current_page: data.current_page, last_page: data.last_page, from: data.from, to: data.to };
    visitantesData = data.data;
    dataLoadedAt   = new Date();
    renderTabla();
    actualizarIconosSort();
    renderTotalYPaginacion();
}

function sortValue(v, field) {
    if (field === 'sede')          return v.sede?.nombre ?? '';
    if (field === 'hora_entrada')  return v.hora_entrada ?? '';
    if (field === 'hora_salida')   return v.hora_salida  ?? '';
    if (field === 'minutos_en_sede') return v.minutos_en_sede ?? 0;
    return (v[field] ?? '').toString().toLowerCase();
}

function renderTabla() {
    const tbody = document.getElementById('visitantesTbody');
    const sorted = [...visitantesData].sort((a, b) => {
        const va = sortValue(a, sortField);
        const vb = sortValue(b, sortField);
        if (va < vb) return sortDir === 'asc' ? -1 :  1;
        if (va > vb) return sortDir === 'asc' ?  1 : -1;
        return 0;
    });

    tbody.innerHTML = sorted.map(v => `
        <tr>
            <td>${v.cedula}</td>
            <td><strong>${v.nombre ?? '—'}</strong></td>
            <td>${v.empresa ?? '—'}</td>
            <td>${v.telefono ?? '—'}</td>
            <td><small>${v.eps ?? '—'} / ${v.arl ?? '—'}</small></td>
            <td>${v.placa ? `<span class="badge bg-secondary">${v.placa}</span>` : '—'}</td>
            <td>${v.persona_visita ?? '—'}</td>
            <td><span class="badge bg-primary">${v.sede?.nombre ?? '—'}</span></td>
            <td><small>${formatDT(v.hora_entrada)}</small></td>
            <td><small>${v.hora_salida ? formatDT(v.hora_salida) : '<span class="badge bg-warning text-dark">En sede</span>'}</small></td>
            <td><small>${tiempoEnSede(v)}</small></td>
            <td>${badgeInduccion(v)}</td>
            <td>${badgeUltimaInduccion(v)}</td>
            <td>
                <div style="max-width:160px">
                    ${v.observacion ? `<small class="text-muted d-block mb-1" style="word-break:break-word">${escHtml(v.observacion)}</small>` : ''}
                    <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="editarObservacion(${v.id})" title="${v.observacion ? 'Editar observación' : 'Agregar observación'}">
                        <i class="ti ti-${v.observacion ? 'edit' : 'plus'}"></i>
                    </button>
                </div>
            </td>
            <td>${v.imagen_entrada ? `<button class="btn btn-sm btn-outline-primary" onclick="verFoto(${v.id})"><i class="ti ti-photo"></i></button>` : '—'}</td>
            <td>${botonForzarSalida(v)}</td>
        </tr>
    `).join('');
}

function actualizarIconosSort() {
    document.querySelectorAll('thead th.sortable').forEach(th => {
        const field = th.dataset.field;
        th.innerHTML = th.textContent.replace(/ [▲▼⇅]$/, '') +
            (field === sortField ? (sortDir === 'asc' ? ' ▲' : ' ▼') : ' ⇅');
    });
}

function renderTotalYPaginacion() {
    const { total, per_page, current_page, last_page, from, to } = paginaMeta;

    // Total label
    document.getElementById('totalLabel').innerHTML =
        `<i class="ti ti-users me-1 text-primary"></i> <strong>${total}</strong> visitante${total !== 1 ? 's' : ''} encontrado${total !== 1 ? 's' : ''}`;

    // Paginación
    const container = document.getElementById('paginacionContainer');
    if (last_page <= 1) {
        container.style.setProperty('display', 'none', 'important');
        return;
    }
    container.style.removeProperty('display');

    document.getElementById('paginacionInfo').textContent = `Mostrando ${from}–${to} de ${total}`;

    const ul = document.getElementById('paginacionLinks');
    ul.innerHTML = '';

    const addLi = (label, page, disabled = false, active = false) => {
        const li = document.createElement('li');
        li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.innerHTML = label;
        if (!disabled && !active) a.addEventListener('click', e => { e.preventDefault(); loadVisitantes(page); });
        li.appendChild(a);
        ul.appendChild(li);
    };

    addLi('&laquo;', current_page - 1, current_page === 1);

    // Ventana de páginas: máximo 5 alrededor de la actual
    const delta = 2;
    const start = Math.max(1, current_page - delta);
    const end   = Math.min(last_page, current_page + delta);

    if (start > 1) { addLi('1', 1); if (start > 2) addLi('…', null, true); }
    for (let p = start; p <= end; p++) addLi(p, p, false, p === current_page);
    if (end < last_page) { if (end < last_page - 1) addLi('…', null, true); addLi(last_page, last_page); }

    addLi('&raquo;', current_page + 1, current_page === last_page);
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

function badgeUltimaInduccion(v) {
    if (!v.ultima_induccion_fecha) {
        return '<span class="text-muted small">Sin registro</span>';
    }
    const color = v.ultima_induccion_vencida ? 'text-danger' : 'text-success';
    const icono = v.ultima_induccion_vencida ? 'ti-alert-triangle' : 'ti-circle-check';
    return `<div>
        <small class="d-block fw-semibold">${v.ultima_induccion_fecha}</small>
        <small class="${color}"><i class="ti ${icono} me-1"></i>${v.ultima_induccion_hace}</small>
    </div>`;
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

function badgeInduccion(v) {
    if (!v.induccion_requerida) {
        return '<span class="badge bg-secondary">No requerida</span>';
    }
    if (v.induccion_fecha) {
        const fecha = formatDT(v.induccion_fecha);
        return `<div>
            <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Realizada</span>
            <small class="d-block text-muted mt-1">${fecha}</small>
            <button class="btn btn-link btn-sm p-0 mt-1 text-secondary" style="font-size:11px" onclick="marcarInduccion(${v.id})" title="Cambiar fecha de inducción">
                <i class="ti ti-calendar-edit me-1"></i>Cambiar fecha
            </button>
        </div>`;
    }
    return `<div class="d-flex flex-column align-items-start gap-1">
        <span class="badge bg-danger"><i class="ti ti-alert-circle me-1"></i>Pendiente</span>
        <button class="btn btn-sm btn-success py-0 px-2 mt-1" onclick="marcarInduccion(${v.id})" title="Registrar fecha de inducción">
            <i class="ti ti-calendar-plus me-1"></i>Registrar fecha
        </button>
    </div>`;
}

async function marcarInduccion(id) {
    const v = visitantesData.find(x => x.id === id);
    // Precarga: si ya tiene fecha, usarla; si no, hoy
    const fechaActual = v?.induccion_fecha
        ? new Date(v.induccion_fecha).toISOString().slice(0, 16)
        : new Date(new Date() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);

    const result = await Swal.fire({
        title: '<i class="ti ti-shield-check me-2 text-success"></i>Fecha de inducción',
        html: `
            <p class="text-muted small mb-3">Ingresa la fecha y hora en que se realizó la inducción.</p>
            <input id="swal-fecha-ind" type="datetime-local" value="${fechaActual}"
                style="width:100%;font-size:14px;border:1px solid #dee2e6;border-radius:8px;padding:9px 12px;outline:none;color:#344054;font-family:inherit;"
                onfocus="this.style.borderColor='#198754';this.style.boxShadow='0 0 0 3px rgba(25,135,84,.15)'"
                onblur="this.style.borderColor='#dee2e6';this.style.boxShadow='none'">`,
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-check me-1"></i>Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: 420,
        padding: '1.5rem',
        customClass: { htmlContainer: 'text-start' },
        preConfirm: () => {
            const val = document.getElementById('swal-fecha-ind').value;
            if (!val) { Swal.showValidationMessage('Selecciona una fecha'); return false; }
            return val;
        },
        didOpen: () => document.getElementById('swal-fecha-ind').focus(),
    });
    if (!result.isConfirmed) return;
    await fetch(`/admin/visitantes/${id}/induccion`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        body: JSON.stringify({ fecha: result.value }),
    });
    loadVisitantes();
}

async function editarObservacion(id) {
    const v = visitantesData.find(x => x.id === id);
    const obsActual = v?.observacion ?? '';
    const result = await Swal.fire({
        title: '<i class="ti ti-notes me-2 text-primary"></i>Observación',
        html: `
            <p class="text-muted small mb-3">Escribe una nota o comentario sobre este registro de visita.</p>
            <textarea id="swal-obs"
                placeholder="Escribe una observación..."
                style="width:100%;height:110px;font-size:13px;border:1px solid #dee2e6;border-radius:8px;padding:10px 12px;resize:vertical;outline:none;font-family:inherit;color:#344054;line-height:1.5;"
                onfocus="this.style.borderColor='#4F46E5';this.style.boxShadow='0 0 0 3px rgba(79,70,229,.15)'"
                onblur="this.style.borderColor='#dee2e6';this.style.boxShadow='none'"
            >${escHtml(obsActual)}</textarea>`,
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-check me-1"></i>Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4F46E5',
        cancelButtonColor: '#6c757d',
        width: 480,
        padding: '1.5rem',
        customClass: { htmlContainer: 'text-start' },
        preConfirm: () => document.getElementById('swal-obs').value.trim(),
        didOpen: () => {
            const ta = document.getElementById('swal-obs');
            ta.focus();
            ta.setSelectionRange(ta.value.length, ta.value.length);
        },
    });
    if (!result.isConfirmed) return;
    await fetch(`/admin/visitantes/${id}/observacion`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        body: JSON.stringify({ observacion: result.value || null }),
    });
    loadVisitantes();
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

document.addEventListener('DOMContentLoaded', () => {
    loadSedes();
    loadVisitantes();

    // Forzar mayúsculas en valor real (no solo visual)
    document.querySelectorAll('.rm-upper').forEach(el => {
        el.addEventListener('input', () => {
            const pos = el.selectionStart;
            el.value = el.value.toUpperCase();
            el.setSelectionRange(pos, pos);
        });
    });

    // Sorting por click en cabeceras
    document.querySelectorAll('thead th.sortable').forEach(th => {
        th.style.cursor = 'pointer';
        th.style.userSelect = 'none';
        th.addEventListener('click', () => {
            const field = th.dataset.field;
            if (sortField === field) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortField = field;
                sortDir   = 'asc';
            }
            renderTabla();
            actualizarIconosSort();
        });
    });
});

// ── Registro manual ──────────────────────────────────────────────────────────
async function buscarPorCedula() {
    const cedula = document.getElementById('rm_cedula').value.trim();
    const msg    = document.getElementById('rm_cedula_msg');
    const btn    = document.getElementById('btnBuscarCedula');

    if (!cedula) {
        msg.className = 'form-text text-warning';
        msg.textContent = 'Ingresa una cédula primero.';
        msg.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    msg.classList.add('d-none');

    try {
        const res  = await fetch(`/admin/visitantes/buscar-cedula?cedula=${encodeURIComponent(cedula)}`);
        const data = await res.json();

        if (data.found) {
            const d = data.data;
            document.getElementById('rm_nombre').value   = d.nombre   ?? '';
            document.getElementById('rm_telefono').value = d.telefono ?? '';
            document.getElementById('rm_empresa').value  = d.empresa  ?? '';
            document.getElementById('rm_eps').value      = d.eps      ?? '';
            document.getElementById('rm_arl').value      = d.arl      ?? '';
            msg.className = 'form-text text-success';
            msg.textContent = '✓ Datos del último registro cargados. Puedes editarlos.';
        } else {
            msg.className = 'form-text text-muted';
            msg.textContent = 'No se encontraron registros anteriores para esta cédula.';
        }
    } catch {
        msg.className = 'form-text text-danger';
        msg.textContent = 'Error al buscar. Intenta de nuevo.';
    } finally {
        msg.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-search"></i>';
    }
}

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
    document.getElementById('rm_cedula_msg').className = 'form-text d-none';
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
