@extends('layouts.admin')
@section('title', 'Permisos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-file-signature me-2 text-primary"></i>Permisos</h4>
                <p class="text-muted mb-0">Gestion de permisos de empleados</p>
            </div>
            @can('permisos.crear')
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Permiso
            </button>
            @else
            <button class="btn btn-primary" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Permiso
            </button>
            @endcan
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3 shadow-lg border-0">
        <div class="card-body p-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Desde</label>
                    <input type="date" id="filterFrom" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Hasta</label>
                    <input type="date" id="filterTo" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Empleado</label>
                    <select id="filterEmpleado" class="form-select form-select-sm">
                        <option value="">Todos los empleados</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Estado</label>
                    <select id="filterEstado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>
                <div class="col-md-auto d-flex align-items-end gap-2">
                    <button class="btn btn-sm btn-primary" onclick="loadPermisos()">
                        <i class="fa-solid fa-search me-1"></i> Filtrar
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="limpiarFiltros()">
                        <i class="fa-solid fa-xmark me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 w-100" id="permisosTable">
                <thead class="table-light">
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Duracion</th>
                        <th>Motivo</th>
                        <th>Creado por</th>
                        <th>Aprobado por</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="permisosTbody">
                    <tr id="trLoadingPerm">
                        <td colspan="10" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                            <p class="text-muted mt-2 mb-0 small">Cargando permisos...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="permisoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Permiso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="permisoId">
                <div class="mb-3">
                    <label class="form-label">Empleado <span class="text-danger">*</span></label>
                    <select id="pEmpleado" class="form-select"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de Permiso <span class="text-danger">*</span></label>
                    <select id="pTipoPermiso" class="form-select">
                        <option value="">-- Seleccionar --</option>
                    </select>
                    <small id="pRemuneradoBadge" class="mt-1 d-none"></small>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Fecha/hora inicio <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="pFechaInicio" class="form-control" onchange="calcularDuracion()">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Fecha/hora fin <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="pFechaFin" class="form-control" onchange="calcularDuracion()">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Duracion calculada</label>
                    <input type="text" id="pDuracion" class="form-control" readonly placeholder="Se calcula automaticamente">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo</label>
                    <textarea id="pMotivo" class="form-control" rows="2" placeholder="Descripcion del permiso..."></textarea>
                </div>
                <div id="permisoError" class="alert alert-danger py-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarPermiso" onclick="savePermiso()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
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
div.dataTables_wrapper div.dataTables_info {
    font-size: 13px;
    color: #6c757d;
}
#permisosTable th, #permisosTable td {
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
const csrfToken = '{{ csrf_token() }}';
const canAprobarPermiso  = {{ auth()->user()->can('permisos.aprobar')  ? 'true' : 'false' }};
const canEliminarPermiso = {{ auth()->user()->can('permisos.eliminar') ? 'true' : 'false' }};
const estadoBadge = {
    pendiente: 'bg-warning text-dark',
    aprobado:  'bg-success',
    rechazado: 'bg-danger',
};
var tablaPermisos = null;
var tiposPermiso  = [];

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    const hoy   = new Date().toISOString().slice(0,10);
    const inicio = new Date(); inicio.setDate(1);
    document.getElementById('filterFrom').value = inicio.toISOString().slice(0,10);
    document.getElementById('filterTo').value   = hoy;
    loadEmpleados();
    loadTiposPermiso();
    loadPermisos();
});

async function loadEmpleados() {
    const res  = await fetch('/admin/empleados/list?per_page=500', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
    const data = await res.json();
    const empleados = data.data || [];
    const sel  = document.getElementById('filterEmpleado');
    const pSel = document.getElementById('pEmpleado');
    pSel.innerHTML = '<option value="">-- Seleccionar --</option>';
    empleados.forEach(e => {
        const opt = `<option value="${e.id}">${e.name} (${e.codigo_empleado})</option>`;
        sel.innerHTML += opt;
        pSel.innerHTML += opt;
    });
}

async function loadTiposPermiso() {
    try {
        const res = await fetch('/admin/permisos/tipos-activos', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        tiposPermiso = await res.json();
        const sel = document.getElementById('pTipoPermiso');
        sel.innerHTML = '<option value="">-- Seleccionar --</option>';
        tiposPermiso.forEach(t => {
            const badge = t.es_remunerado ? '(Remunerado)' : '(No remunerado)';
            sel.innerHTML += `<option value="${t.id}" data-remunerado="${t.es_remunerado}">${t.nombre} ${badge}</option>`;
        });
    } catch(e) { console.error('loadTiposPermiso:', e); }
}

function onTipoChange() {
    const sel = document.getElementById('pTipoPermiso');
    const opt = sel.options[sel.selectedIndex];
    const badge = document.getElementById('pRemuneradoBadge');
    if (opt && opt.value) {
        const esRem = opt.dataset.remunerado === '1' || opt.dataset.remunerado === 'true';
        badge.className = esRem ? 'badge bg-success mt-1' : 'badge bg-danger mt-1';
        badge.textContent = esRem ? 'Remunerado' : 'No remunerado';
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('pTipoPermiso').addEventListener('change', onTipoChange);
});

function calcularDuracion() {
    const inicio = document.getElementById('pFechaInicio').value;
    const fin    = document.getElementById('pFechaFin').value;
    const el     = document.getElementById('pDuracion');
    if (!inicio || !fin) { el.value = ''; return; }

    const ms   = new Date(fin) - new Date(inicio);
    if (ms <= 0) { el.value = 'Fecha fin debe ser posterior'; return; }

    const totalMin = Math.round(ms / 60000);
    const dias     = Math.floor(totalMin / (24 * 60));
    const horas    = Math.floor((totalMin % (24 * 60)) / 60);
    const mins     = totalMin % 60;

    let txt = '';
    if (dias > 0) txt += dias + 'd ';
    if (horas > 0) txt += horas + 'h ';
    if (mins > 0) txt += mins + 'min';
    el.value = txt.trim() || '0min';
}

function formatDuracion(fechaInicio, fechaFin) {
    if (!fechaInicio || !fechaFin) return '—';
    const ms = new Date(fechaFin) - new Date(fechaInicio);
    if (ms <= 0) return '—';
    const totalMin = Math.round(ms / 60000);
    const dias  = Math.floor(totalMin / (24 * 60));
    const horas = Math.floor((totalMin % (24 * 60)) / 60);
    const mins  = totalMin % 60;
    let txt = '';
    if (dias > 0) txt += dias + 'd ';
    if (horas > 0) txt += horas + 'h ';
    if (mins > 0) txt += mins + 'min';
    return txt.trim() || '0min';
}

function formatFecha(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric' })
         + ' ' + d.toLocaleTimeString('es-CO', { hour:'2-digit', minute:'2-digit' });
}

async function loadPermisos() {
    const from   = document.getElementById('filterFrom').value;
    const to     = document.getElementById('filterTo').value;
    const userId = document.getElementById('filterEmpleado').value;
    const estado = document.getElementById('filterEstado').value;
    let url = `/admin/permisos/list?per_page=1000`;
    if (from)   url += `&date_from=${from}`;
    if (to)     url += `&date_to=${to}`;
    if (userId) url += `&user_id=${userId}`;
    if (estado) url += `&estado=${estado}`;

    try {
        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        const items = data.data || [];

        var trLoading = document.getElementById('trLoadingPerm');
        if (trLoading) trLoading.remove();

        if ($.fn.DataTable.isDataTable('#permisosTable')) {
            tablaPermisos.clear().rows.add(items).draw();
        } else {
            tablaPermisos = $('#permisosTable').DataTable({
                data: items,
                processing: true,
                order: [[2, 'desc']],
                scrollX: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    lengthMenu: 'Mostrar _MENU_ registros',
                    zeroRecords: 'Sin permisos',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros)',
                    search: 'Buscar:',
                    paginate: { first: 'Primero', last: 'Ultimo', next: 'Siguiente', previous: 'Anterior' },
                    processing: 'Procesando...',
                },
                drawCallback: function() {
                    document.querySelectorAll('#permisosTable [data-bs-toggle="tooltip"]').forEach(el => {
                        bootstrap.Tooltip.getOrCreateInstance(el);
                    });
                },
                initComplete: function() {
                    $('#permisosTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                    $('#permisosTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                },
                columns: [
                    {
                        title: 'Empleado',
                        data: 'user',
                        render: function(data, type) {
                            if (type !== 'display') return data ? data.name : '';
                            return data
                                ? '<strong>' + data.name + '</strong><br><small class="text-muted">' + (data.codigo_empleado || '') + '</small>'
                                : 'N/A';
                        }
                    },
                    {
                        title: 'Tipo',
                        data: 'tipo_permiso',
                        render: function(data, type, row) {
                            if (!data) {
                                // Permiso viejo con tipo ENUM
                                const labels = { salida_temprana:'Salida Temprana', llegada_tarde:'Llegada Tarde', dia_completo:'Dia Completo', horas:'Horas' };
                                return '<span class="badge bg-secondary">' + (labels[row.tipo] || row.tipo || '—') + '</span>';
                            }
                            const badge = data.es_remunerado
                                ? '<span class="badge bg-success ms-1" style="font-size:9px;">Rem</span>'
                                : '<span class="badge bg-danger ms-1" style="font-size:9px;">No rem</span>';
                            return data.nombre + badge;
                        }
                    },
                    {
                        title: 'Fecha Inicio',
                        data: null,
                        render: function(data, type, row) {
                            if (row.fecha_inicio) return formatFecha(row.fecha_inicio);
                            return row.fecha ? row.fecha.slice(0,10) : '—';
                        }
                    },
                    {
                        title: 'Fecha Fin',
                        data: 'fecha_fin',
                        render: function(data) { return data ? formatFecha(data) : '—'; }
                    },
                    {
                        title: 'Duracion',
                        data: null,
                        render: function(data, type, row) {
                            if (row.fecha_inicio && row.fecha_fin) return formatDuracion(row.fecha_inicio, row.fecha_fin);
                            if (row.horas_permiso) return row.horas_permiso + 'h';
                            return '—';
                        }
                    },
                    {
                        title: 'Motivo',
                        data: 'motivo',
                        render: function(data) {
                            return '<small class="text-muted">' + (data || '—') + '</small>';
                        }
                    },
                    {
                        title: 'Creado por',
                        data: 'creador',
                        render: function(data) {
                            return data ? '<small>' + data.name + '</small>' : '<small class="text-muted">—</small>';
                        }
                    },
                    {
                        title: 'Aprobado por',
                        data: 'aprobador',
                        render: function(data) {
                            return data ? '<small>' + data.name + '</small>' : '<small class="text-muted">—</small>';
                        }
                    },
                    {
                        title: 'Estado',
                        data: 'estado',
                        render: function(data, type) {
                            if (type !== 'display') return data;
                            return '<span class="badge ' + (estadoBadge[data] ?? 'bg-secondary') + '">' + data + '</span>';
                        }
                    },
                    {
                        title: 'Acciones',
                        data: 'id',
                        orderable: false,
                        render: function(data, type, row) {
                            var btns = '';
                            if (row.estado === 'pendiente') {
                                btns += canAprobarPermiso
                                    ? '<button class="btn btn-sm btn-success me-1" onclick="aprobar(' + data + ')" title="Aprobar"><i class="fa-solid fa-check"></i></button>'
                                    : '<button class="btn btn-sm btn-success me-1" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;"><i class="fa-solid fa-check"></i></button>';
                                btns += canAprobarPermiso
                                    ? '<button class="btn btn-sm btn-danger me-1" onclick="rechazar(' + data + ')" title="Rechazar"><i class="fa-solid fa-xmark"></i></button>'
                                    : '<button class="btn btn-sm btn-danger me-1" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;"><i class="fa-solid fa-xmark"></i></button>';
                            }
                            btns += canEliminarPermiso
                                ? '<button class="btn btn-sm btn-outline-danger" onclick="eliminar(' + data + ')"><i class="fa-solid fa-trash"></i></button>'
                                : '<button class="btn btn-sm btn-outline-danger" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;"><i class="fa-solid fa-trash"></i></button>';
                            return btns;
                        }
                    }
                ]
            });
        }
    } catch(e) {
        console.error(e);
    }
}

function limpiarFiltros() {
    const hoy = new Date().toISOString().slice(0,10);
    const inicio = new Date(); inicio.setDate(1);
    document.getElementById('filterFrom').value     = inicio.toISOString().slice(0,10);
    document.getElementById('filterTo').value       = hoy;
    document.getElementById('filterEmpleado').value = '';
    document.getElementById('filterEstado').value   = '';
    loadPermisos();
}

function openModal() {
    document.getElementById('permisoId').value = '';
    document.getElementById('pEmpleado').value = '';
    document.getElementById('pTipoPermiso').value = '';
    document.getElementById('pFechaInicio').value = '';
    document.getElementById('pFechaFin').value = '';
    document.getElementById('pDuracion').value = '';
    document.getElementById('pMotivo').value = '';
    document.getElementById('pRemuneradoBadge').classList.add('d-none');
    document.getElementById('modalTitle').textContent = 'Nuevo Permiso';
    document.getElementById('permisoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('permisoModal')).show();
}

async function savePermiso() {
    const userId       = document.getElementById('pEmpleado').value;
    const tipoId       = document.getElementById('pTipoPermiso').value;
    const fechaInicio  = document.getElementById('pFechaInicio').value;
    const fechaFin     = document.getElementById('pFechaFin').value;

    if (!userId || !tipoId || !fechaInicio || !fechaFin) {
        const el = document.getElementById('permisoError');
        el.textContent = 'Empleado, tipo, fecha inicio y fecha fin son obligatorios.';
        el.style.display = 'block';
        return;
    }

    const btn = document.getElementById('btnGuardarPermiso');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

    const payload = {
        user_id:         parseInt(userId),
        tipo_permiso_id: parseInt(tipoId),
        fecha_inicio:    fechaInicio,
        fecha_fin:       fechaFin,
        motivo:          document.getElementById('pMotivo').value,
    };

    try {
        const res = await fetch('/admin/permisos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload),
        });
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('permisoModal')).hide();
            loadPermisos();
        } else {
            const err = await res.json();
            const el = document.getElementById('permisoError');
            el.textContent = Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error';
            el.style.display = 'block';
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Guardar';
    }
}

async function aprobar(id) {
    if (!confirm('Aprobar este permiso?')) return;
    await fetch(`/admin/permisos/${id}/aprobar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadPermisos();
}

async function rechazar(id) {
    if (!confirm('Rechazar este permiso?')) return;
    await fetch(`/admin/permisos/${id}/rechazar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadPermisos();
}

async function eliminar(id) {
    if (!confirm('Eliminar este permiso?')) return;
    await fetch(`/admin/permisos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadPermisos();
}
</script>
@endpush
