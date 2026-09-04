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
                            <button class="btn btn-sm btn-secondary" onclick="limpiarFiltros()"><i class="fa-solid fa-xmark me-1"></i> Limpiar</button>
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
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 w-100" id="attendanceTable">
                        <thead class="table-light"></thead>
                        <tbody>
                            <tr id="trLoadingAtt">
                                <td colspan="10" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                                    <p class="text-muted mt-2 mb-0 small">Cargando registros...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
/* Controles DataTable dentro del card */
div.dataTables_wrapper div.dataTables_length,
div.dataTables_wrapper div.dataTables_filter {
    padding: 12px 16px 0;
}
div.dataTables_wrapper div.dataTables_info,
div.dataTables_wrapper div.dataTables_paginate {
    padding: 10px 16px 12px;
    border-top: 1px solid #e9ecef;
}
div.dataTables_wrapper div.dataTables_length label,
div.dataTables_wrapper div.dataTables_filter label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 8px;
}
div.dataTables_wrapper div.dataTables_filter input:focus {
    outline: none;
    border-color: #4F46E5;
    box-shadow: 0 0 0 3px rgba(79,70,229,.1);
}
div.dataTables_wrapper div.dataTables_info {
    font-size: 13px;
    color: #6c757d;
}
#attendanceTable th, #attendanceTable td {
    font-size: 13px;
    vertical-align: middle;
    white-space: nowrap;
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const csrfToken  = '{{ csrf_token() }}';
const isEmpleado = {{ auth()->user()->role === 'empleado' ? 'true' : 'false' }};
const canViewPhoto = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
const myUserId   = {{ auth()->id() }};
var tablaAtt = null;

async function loadRecords() {
    const from   = document.getElementById('reportFrom').value;
    const to     = document.getElementById('reportTo').value;
    const tipo   = document.getElementById('filterTipo').value;
    const metodo = document.getElementById('filterMetodo').value;
    const search = document.getElementById('filterSearch').value;
    const empId  = isEmpleado ? myUserId : document.getElementById('filterEmpleado').value;
    let url = `/admin/attendance/records?per_page=1000`;
    if (from)   url += `&date_from=${from}`;
    if (to)     url += `&date_to=${to}`;
    if (tipo)   url += `&tipo=${tipo}`;
    if (metodo) url += `&metodo=${metodo}`;
    if (empId)  url += `&user_id=${empId}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    try {
        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();
        const datos = json.data || [];

        var trLoading = document.getElementById('trLoadingAtt');
        if (trLoading) trLoading.remove();

        if (tablaAtt) {
            tablaAtt.clear().rows.add(datos).draw();
            colVisAttApply(colVisAttGetState());
        } else {
            tablaAtt = $('#attendanceTable').DataTable({
                data: datos,
                processing: true,
                order: [[4, 'desc']],
                scrollX: true,
                pageLength: 20,
                lengthMenu: [10, 20, 50, 100],
                language: {
                    lengthMenu: 'Mostrar _MENU_ registros',
                    zeroRecords: 'Sin registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros)',
                    search: 'Buscar:',
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                },
                initComplete: function() {
                    $('#attendanceTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                    $('#attendanceTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                    $('#attendanceTable_filter').prepend(
                        '<button id="btnReloadAtt" class="btn btn-sm btn-outline-secondary me-2" onclick="loadRecords()" title="Recargar tabla">' +
                        '<i class="ti ti-refresh"></i></button>'
                    );
                },
                drawCallback: function() {
                    document.querySelectorAll('#attendanceTable [data-bs-toggle="tooltip"]').forEach(function(el) {
                        new bootstrap.Tooltip(el, { trigger: 'hover' });
                    });
                },
                columns: [
                    {
                        title: 'Empleado',
                        data: 'user',
                        className: 'col-att-empleado',
                        render: function(data, type, r) {
                            if (type !== 'display') return data ? data.name : '';
                            var avatar = r.foto_perfil_thumbnail
                                ? '<img src="' + r.foto_perfil_thumbnail + '" onclick="verFotoPerfil(\'' + ((r.user && r.user.name)||'').replace(/'/g,"") + '\',\'' + r.foto_perfil_thumbnail + '\',' + r.user_id + ')" style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:2px solid #1ab394;flex-shrink:0;cursor:pointer;" title="Ver foto de perfil" alt="">'
                                : '<span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e9ecef;color:#adb5bd;font-size:15px;flex-shrink:0;"><i class="fa-solid fa-user"></i></span>';
                            var horarioIcon = (function() {
                                if (!r.horario) return '<i class="fa-solid fa-circle-question text-light me-1" data-bs-toggle="tooltip" title="Sin horario asignado"></i>';
                                var fecha = new Date(r.fecha_hora);
                                var isoDay = fecha.getDay() === 0 ? 7 : fecha.getDay();
                                var dia = (r.horario.dias || []).find(function(d) { return d.dia_semana === isoDay; });
                                var diaLabel = ['','Lun','Mar','Mié','Jue','Vie','Sáb','Dom'][isoDay] || '';
                                var horaInfo = dia
                                    ? diaLabel + ': ' + (dia.hora_entrada||'').slice(0,5) + '–' + (dia.hora_salida||'').slice(0,5) + (dia.retardo_min ? ' | Retardo: ' + dia.retardo_min + ' min' : '')
                                    : diaLabel + ': día no laboral';
                                return '<i class="fa-solid fa-circle-question text-muted me-1" style="cursor:default;" data-bs-toggle="tooltip" title="Horario: ' + r.horario.nombre + ' | ' + horaInfo + '"></i>';
                            })();
                            return '<div class="d-flex align-items-center gap-2">' + avatar +
                                '<div>' + horarioIcon + '<strong>' + ((r.user && r.user.name) || 'N/A') + '</strong></div></div>';
                        }
                    },
                    {
                        title: 'Código',
                        data: 'user',
                        className: 'col-att-codigo',
                        render: function(data, type) {
                            if (type !== 'display') return data ? data.codigo_empleado : '';
                            return data ? '<span class="badge bg-primary">' + (data.codigo_empleado || '—') + '</span>' : '—';
                        }
                    },
                    {
                        title: 'Sede',
                        data: 'sede',
                        className: 'col-att-sede',
                        render: function(data, type) {
                            if (type !== 'display') return data ? data.nombre : '';
                            return data ? '<span class="badge bg-primary">' + data.nombre + '</span>' : '—';
                        }
                    },
                    {
                        title: 'Tipo',
                        data: 'tipo',
                        className: 'col-att-tipo',
                        render: function(data, type) {
                            if (type !== 'display') return data;
                            return '<span class="badge ' + (data.includes('entrada') ? 'bg-success' : 'bg-danger') + '">' + data.replace(/_/g, ' ') + '</span>';
                        }
                    },
                    {
                        title: 'Fecha/Hora',
                        data: 'fecha_hora',
                        className: 'col-att-fecha',
                        render: function(data, type, r) {
                            if (type !== 'display') return data;
                            var fechaStr = new Date(data).toLocaleString('es-CO', {timeZone: 'America/Bogota'});
                            if (r.tipo !== 'entrada' || !r.horario) return fechaStr;
                            var fecha  = new Date(data);
                            var isoDay = fecha.getDay() === 0 ? 7 : fecha.getDay();
                            var dia    = (r.horario.dias || []).find(function(d) { return d.dia_semana === isoDay; });
                            if (!dia || !dia.hora_entrada) return fechaStr;
                            var parts  = dia.hora_entrada.split(':').map(Number);
                            var limite = new Date(fecha);
                            limite.setHours(parts[0], parts[1] + (dia.retardo_min || 0), 0, 0);
                            var tarde  = fecha > limite;
                            var icono  = tarde
                                ? '<i class="fa-solid fa-circle-exclamation text-danger me-1" title="Tardanza"></i>'
                                : '<i class="fa-solid fa-circle-check text-success me-1" title="A tiempo"></i>';
                            return icono + fechaStr;
                        }
                    },
                    {
                        title: 'Método',
                        data: 'metodo',
                        className: 'col-att-metodo',
                        render: function(data, type) {
                            if (type !== 'display') return data;
                            return '<span class="badge bg-info">' + data + '</span>';
                        }
                    },
                    {
                        title: 'QR',
                        data: 'qr_validado',
                        className: 'col-att-qr',
                        render: function(data, type) {
                            if (type !== 'display') return data ? 'Sí' : 'No';
                            return '<span class="badge ' + (data ? 'bg-success' : 'bg-danger') + '">' + (data ? 'Sí' : 'No') + '</span>';
                        }
                    },
                    {
                        title: 'Geocerca',
                        data: 'geocerca_validada',
                        className: 'col-att-geocerca',
                        render: function(data, type) {
                            if (type !== 'display') return data ? 'Sí' : 'No';
                            return '<span class="badge ' + (data ? 'bg-success' : 'bg-danger') + '">' + (data ? 'Sí' : 'No') + '</span>';
                        }
                    },
                    {
                        title: 'Distancia',
                        data: 'distancia_oficina_mts',
                        className: 'col-att-distancia',
                        render: function(data) {
                            return data ? data + 'm' : '—';
                        }
                    },
                    {
                        title: 'Foto',
                        data: 'foto_evidencia',
                        className: 'col-att-foto',
                        orderable: false,
                        render: function(data, type, r) {
                            if (type !== 'display') return data || '';
                            return data === 'base64'
                                ? '<button class="btn btn-sm btn-outline-primary" onclick="verFoto(' + r.id + ')" title="Ver foto" ' + (canViewPhoto ? '' : 'disabled') + '><i class="fa-solid fa-camera"></i></button>'
                                : '<span class="text-muted">—</span>';
                        }
                    }
                ]
            });
            colVisAttApply(colVisAttGetState());
        }
    } catch(e) {
        console.error('loadRecords error:', e);
        alert('Error al cargar registros: ' + e.message);
    }
}

function limpiarFiltros() {
    const today = '{{ date('Y-m-d') }}';
    document.getElementById('filterSearch').value = '';
    document.getElementById('reportFrom').value = today;
    document.getElementById('reportTo').value = today;
    document.getElementById('filterTipo').value = '';
    document.getElementById('filterMetodo').value = '';
    if (!isEmpleado) document.getElementById('filterEmpleado').value = '';
    loadRecords();
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

async function verFotoPerfil(nombre, thumbnail, userId) {
    const img = document.getElementById('modalFotoPerfilImg');
    document.getElementById('modalFotoPerfilNombre').textContent = nombre;
    img.src = thumbnail;
    new bootstrap.Modal(document.getElementById('modalFotoPerfil')).show();
    try {
        const res  = await fetch(`/admin/empleados/${userId}/imagen-perfil`);
        const data = await res.json();
        if (data.imagen_base64) img.src = data.imagen_base64;
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', function() {
    if (isEmpleado) {
        document.getElementById('filterEmpleado').closest('.col-md-3').style.display = 'none';
    } else {
        loadEmpleadosFilter();
    }
    loadRecords();
    document.getElementById('filterSearch').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') loadRecords();
    });
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
    if (!tablaAtt) return;
    COL_VIS_DEFS_ATT.forEach(function(c, i) {
        tablaAtt.column(i).visible(!!state[c.cls], false);
    });
    tablaAtt.columns.adjust();
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

// Aplicar visibilidad guardada una vez la tabla esté lista
var _colVisAttInterval = setInterval(function() {
    if (tablaAtt) {
        colVisAttApply(colVisAttGetState());
        clearInterval(_colVisAttInterval);
    }
}, 200);
</script>
@endpush
