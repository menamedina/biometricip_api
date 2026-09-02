@extends('layouts.admin')
@section('title', 'Registros de Asistencia')

@section('content')
<!-- Modal Foto -->
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-camera me-2"></i>Foto de evidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="modalFotoBody">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <h4 class="mb-1"><i class="fa-solid fa-clock me-2 text-primary"></i>Registros de Asistencia</h4>
            <p class="text-muted mb-0">Historial completo de entradas y salidas</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-2">
                    <div class="row g-2 mb-2 align-items-end">
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Buscar...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="reportFrom" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="reportTo" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-auto d-flex align-items-end gap-2">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosExtraAtt" title="Más filtros">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="loadRecords()"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
                            <button class="btn btn-sm btn-success" onclick="exportCSV()"><i class="fa-solid fa-file-csv me-1"></i> Exportar CSV</button>
                        </div>
                    </div>
                    <div class="collapse" id="filtrosExtraAtt">
                        <div class="row g-2 mb-2">
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" id="filterTipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="entrada">Entrada</option>
                                    <option value="salida_almuerzo">Salida Almuerzo</option>
                                    <option value="regreso_almuerzo">Regreso Almuerzo</option>
                                    <option value="salida">Salida</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" id="filterMetodo">
                                    <option value="">Todos los métodos</option>
                                    <option value="qr">QR</option>
                                    <option value="biometrico">Biométrico</option>
                                    <option value="reconocimiento_facial">Reconocimiento facial</option>
                                    <option value="foto">Foto</option>
                                    <option value="qr_web">QR Web</option>
                                    <option value="manual">Manual</option>
                                    <option value="dispositivo">Dispositivo</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filterEmpleado">
                                    <option value="">Todos los empleados</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="col-att-empleado">Empleado</th>
                                <th class="col-att-codigo">Código</th>
                                <th class="col-att-sede">Sede</th>
                                <th class="col-att-tipo">Tipo</th>
                                <th class="col-att-fecha">Fecha/Hora</th>
                                <th class="col-att-metodo">Método</th>
                                <th class="col-att-qr">QR</th>
                                <th class="col-att-geocerca">Geocerca</th>
                                <th class="col-att-distancia">Distancia</th>
                                <th class="col-att-foto">Foto</th>
                            </tr>
                        </thead>
                        <tbody id="recordsTbody">
                            <tr><td colspan="10" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="recordsInfo"></small>
                    <div id="recordsPagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Botón flotante visibilidad de columnas --}}
<div id="colVisBtnAtt" title="Mostrar / ocultar columnas"
     style="position:fixed;bottom:70px;right:28px;z-index:1055;cursor:pointer;
            width:48px;height:48px;border-radius:50%;background:#1ab394;
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 4px 14px rgba(0,0,0,.25);transition:background .2s;"
     onmouseenter="this.style.background='#17a07d'" onmouseleave="this.style.background='#1ab394'"
     onclick="toggleColVisPanelAtt()">
    <i class="ti ti-settings text-white" style="font-size:22px;"></i>
</div>

<div id="colVisPanelAtt"
     style="display:none;position:fixed;bottom:128px;right:28px;z-index:1056;
            background:#fff;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.18);
            min-width:220px;padding:14px 16px;">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="fw-semibold small">Columnas visibles</span>
        <button class="btn btn-link btn-sm p-0 text-muted" onclick="toggleColVisPanelAtt()">
            <i class="ti ti-x"></i>
        </button>
    </div>
    <div id="colVisChecksAtt"></div>
    <div class="mt-2 pt-2 border-top d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="colVisAttTodos(true)">Todos</button>
        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="colVisAttTodos(false)">Ninguno</button>
    </div>
</div>
{{-- Modal previsualización foto de perfil --}}
<div class="modal fade" id="modalFotoPerfil" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalFotoPerfilNombre"></h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center">
                <img id="modalFotoPerfilImg" src="" alt="Foto de perfil"
                     style="width:100%;max-width:260px;border-radius:12px;object-fit:cover;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken  = '{{ csrf_token() }}';
const isEmpleado = {{ auth()->user()->role === 'empleado' ? 'true' : 'false' }};
const canViewPhoto = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
const myUserId   = {{ auth()->id() }};
let recordsPage  = 1;

async function loadRecords(page = 1) {
    recordsPage = page;
    const from   = document.getElementById('reportFrom').value;
    const to     = document.getElementById('reportTo').value;
    const tipo   = document.getElementById('filterTipo').value;
    const metodo = document.getElementById('filterMetodo').value;
    const search = document.getElementById('filterSearch').value;
    const empId  = isEmpleado ? myUserId : document.getElementById('filterEmpleado').value;
    let url = `/admin/attendance/records?page=${page}&per_page=20`;
    if (from)   url += `&date_from=${from}`;
    if (to)     url += `&date_to=${to}`;
    if (tipo)   url += `&tipo=${tipo}`;
    if (metodo) url += `&metodo=${metodo}`;
    if (empId)  url += `&user_id=${empId}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    try {
        const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        const tbody = document.getElementById('recordsTbody');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Sin registros</td></tr>';
        } else {
            tbody.innerHTML = data.data.map(r => {
                const tienefoto = r.foto_evidencia === 'base64';
                const fotoHtml = tienefoto
                    ? `<button class="btn btn-sm btn-outline-primary" onclick="verFoto(${r.id})" title="Ver foto" ${canViewPhoto ? '' : 'disabled'}>
                         <i class="fa-solid fa-camera"></i>
                       </button>`
                    : '<span class="text-muted">—</span>';
                return `
                <tr>
                    <td class="col-att-empleado">
                        <div class="d-flex align-items-center gap-2">
                            ${r.foto_perfil_thumbnail
                                ? `<img src="${r.foto_perfil_thumbnail}" onclick="verFotoPerfil('${(r.user?.name||'').replace(/'/g,'')}','${r.foto_perfil_thumbnail}')" style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:2px solid #1ab394;flex-shrink:0;cursor:pointer;" title="Ver foto de perfil" alt="">`
                                : `<span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e9ecef;color:#adb5bd;font-size:15px;flex-shrink:0;"><i class="fa-solid fa-user"></i></span>`
                            }
                            <div>
                                ${(function() {
                                    if (!r.horario) return '<i class="fa-solid fa-circle-question text-light me-1" data-bs-toggle="tooltip" title="Sin horario asignado"></i>';
                                    const fecha = new Date(r.fecha_hora);
                                    const isoDay = fecha.getDay() === 0 ? 7 : fecha.getDay();
                                    const dia = (r.horario.dias || []).find(d => d.dia_semana === isoDay);
                                    const diaLabel = ['','Lun','Mar','Mié','Jue','Vie','Sáb','Dom'][isoDay] || '';
                                    const horaInfo = dia
                                        ? `${diaLabel}: ${dia.hora_entrada?.slice(0,5)}–${dia.hora_salida?.slice(0,5)}${dia.retardo_min ? ' | Retardo: ' + dia.retardo_min + ' min' : ''}`
                                        : `${diaLabel}: día no laboral`;
                                    return `<i class="fa-solid fa-circle-question text-muted me-1" style="cursor:default;"
                                                data-bs-toggle="tooltip"
                                                title="Horario: ${r.horario.nombre} | ${horaInfo}"></i>`;
                                })()}<strong>${r.user?.name || 'N/A'}</strong>
                            </div>
                        </div>
                    </td>
                    <td class="col-att-codigo"><span class="badge bg-primary">${r.user?.codigo_empleado || '—'}</span></td>
                    <td class="col-att-sede">${r.sede?.nombre || '—'}</td>
                    <td class="col-att-tipo"><span class="badge ${r.tipo.includes('entrada') ? 'bg-success' : 'bg-danger'}">${r.tipo.replace(/_/g, ' ')}</span></td>
                    <td class="col-att-fecha">${(function() {
                            const fechaStr = new Date(r.fecha_hora).toLocaleString('es-CO', {timeZone: 'America/Bogota'});
                            if (r.tipo !== 'entrada') return fechaStr;
                            if (!r.horario) return fechaStr;
                            const fecha   = new Date(r.fecha_hora);
                            const isoDay  = fecha.getDay() === 0 ? 7 : fecha.getDay();
                            const dia     = (r.horario.dias || []).find(d => d.dia_semana === isoDay);
                            if (!dia?.hora_entrada) return fechaStr;
                            const [hE, mE] = dia.hora_entrada.split(':').map(Number);
                            const limite = new Date(fecha);
                            limite.setHours(hE, mE + (dia.retardo_min || 0), 0, 0);
                            const tarde = fecha > limite;
                            const icono = tarde
                                ? '<i class="fa-solid fa-circle-exclamation text-danger me-1" title="Tardanza"></i>'
                                : '<i class="fa-solid fa-circle-check text-success me-1" title="A tiempo"></i>';
                            return icono + fechaStr;
                        })()}</td>
                    <td class="col-att-metodo"><span class="badge bg-info">${r.metodo}</span></td>
                    <td class="col-att-qr"><span class="badge ${r.qr_validado ? 'bg-success' : 'bg-danger'}">${r.qr_validado ? 'Sí' : 'No'}</span></td>
                    <td class="col-att-geocerca"><span class="badge ${r.geocerca_validada ? 'bg-success' : 'bg-danger'}">${r.geocerca_validada ? 'Sí' : 'No'}</span></td>
                    <td class="col-att-distancia">${r.distancia_oficina_mts ? r.distancia_oficina_mts + 'm' : '—'}</td>
                    <td class="col-att-foto">${fotoHtml}</td>
                </tr>`;
            }).join('');
        }
        document.getElementById('recordsInfo').textContent = `${data.total || 0} registros`;
        renderRecordsPagination(data);
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        colVisAttApply(colVisAttGetState());
    } catch(e) { console.error(e); }
}

function renderRecordsPagination(data) {
    const nav = document.getElementById('recordsPagination');
    if (!data.last_page || data.last_page <= 1) { nav.innerHTML = ''; return; }
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    for (let i = 1; i <= data.last_page; i++) {
        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="loadRecords(${i});return false">${i}</a></li>`;
    }
    html += '</ul></nav>';
    nav.innerHTML = html;
}

async function loadEmpleadosFilter() {
    try {
        const res = await fetch('/admin/empleados/list?per_page=200', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        const sel = document.getElementById('filterEmpleado');
        (data.data || []).forEach(e => {
            sel.innerHTML += `<option value="${e.id}">${e.name || ''} (${e.codigo_empleado})</option>`;
        });
    } catch(e) {}
}

function exportCSV() {
    const from = document.getElementById('reportFrom').value;
    const to = document.getElementById('reportTo').value;
    window.open(`/admin/reports/export?date_from=${from}&date_to=${to}`, '_blank');
}

async function verFoto(id) {
    const body = document.getElementById('modalFotoBody');
    body.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
    const modal = new bootstrap.Modal(document.getElementById('modalFoto'));
    modal.show();
    try {
        const res = await fetch(`/admin/attendance/${id}/photo`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!res.ok) { body.innerHTML = '<p class="text-muted">Sin foto disponible</p>'; return; }
        const data = await res.json();
        body.innerHTML = `<img src="${data.foto_base64}" class="img-fluid rounded" style="max-height:500px;">`;
    } catch(e) {
        body.innerHTML = '<p class="text-danger">Error al cargar la foto</p>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (isEmpleado) {
        document.getElementById('filterEmpleado').closest('.col-md-3').style.display = 'none';
    } else {
        loadEmpleadosFilter();
    }
    loadRecords();
    colVisAttApply(colVisAttGetState());
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#colVisPanelAtt, #colVisBtnAtt')) {
            document.getElementById('colVisPanelAtt').style.display = 'none';
        }
    });
});

// ── Visibilidad de columnas (attendance) ─────────────────────────────────────
var COL_VIS_KEY_ATT = 'attendance_col_vis';
var COL_VIS_DEFS_ATT = [
    { cls: 'col-att-empleado',  label: 'Empleado',   default: true  },
    { cls: 'col-att-codigo',    label: 'Código',      default: true  },
    { cls: 'col-att-sede',      label: 'Sede',        default: true  },
    { cls: 'col-att-tipo',      label: 'Tipo',        default: true  },
    { cls: 'col-att-fecha',     label: 'Fecha/Hora',  default: true  },
    { cls: 'col-att-metodo',    label: 'Método',      default: true  },
    { cls: 'col-att-qr',        label: 'QR',          default: false },
    { cls: 'col-att-geocerca',  label: 'Geocerca',    default: false },
    { cls: 'col-att-distancia', label: 'Distancia',   default: false },
    { cls: 'col-att-foto',      label: 'Foto',        default: true  },
];

function colVisAttGetState() {
    try {
        var stored = localStorage.getItem(COL_VIS_KEY_ATT);
        if (stored) return JSON.parse(stored);
    } catch(e) {}
    var state = {};
    COL_VIS_DEFS_ATT.forEach(function(c) { state[c.cls] = c.default; });
    return state;
}

function colVisAttSaveState(state) {
    localStorage.setItem(COL_VIS_KEY_ATT, JSON.stringify(state));
}

function colVisAttApply(state) {
    COL_VIS_DEFS_ATT.forEach(function(c) {
        var visible = !!state[c.cls];
        document.querySelectorAll('.' + c.cls).forEach(function(el) {
            el.style.display = visible ? '' : 'none';
        });
    });
}

function colVisAttBuildPanel() {
    var state = colVisAttGetState();
    var container = document.getElementById('colVisChecksAtt');
    container.innerHTML = '';
    COL_VIS_DEFS_ATT.forEach(function(c) {
        var checked = state[c.cls] ? 'checked' : '';
        var div = document.createElement('div');
        div.className = 'form-check form-switch mb-1';
        div.innerHTML =
            '<input class="form-check-input" type="checkbox" id="colvisAtt_' + c.cls + '" ' + checked + ' onchange="colVisAttToggle(\'' + c.cls + '\', this.checked)">' +
            '<label class="form-check-label small" for="colvisAtt_' + c.cls + '">' + c.label + '</label>';
        container.appendChild(div);
    });
}

function colVisAttToggle(cls, visible) {
    var state = colVisAttGetState();
    state[cls] = visible;
    colVisAttSaveState(state);
    colVisAttApply(state);
}

function colVisAttTodos(visible) {
    var state = colVisAttGetState();
    COL_VIS_DEFS_ATT.forEach(function(c) { state[c.cls] = visible; });
    colVisAttSaveState(state);
    colVisAttApply(state);
    COL_VIS_DEFS_ATT.forEach(function(c) {
        var el = document.getElementById('colvisAtt_' + c.cls);
        if (el) el.checked = visible;
    });
}

function toggleColVisPanelAtt() {
    var panel = document.getElementById('colVisPanelAtt');
    if (panel.style.display === 'none') {
        colVisAttBuildPanel();
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

function verFotoPerfil(nombre, thumbnail) {
    document.getElementById('modalFotoPerfilNombre').textContent = nombre;
    document.getElementById('modalFotoPerfilImg').src = thumbnail;
    new bootstrap.Modal(document.getElementById('modalFotoPerfil')).show();
}
</script>
@endpush
