@extends('layouts.admin')
@section('title', 'Permisos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-file-signature me-2 text-primary"></i>Permisos</h4>
                <p class="text-muted mb-0">Gestión de permisos de empleados</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Permiso
            </button>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
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
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 w-100" id="permisosTable">
                <thead class="table-light">
                    <tr>
                        <th>Empleado</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Horas</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="permisosTbody">
                    <tr id="trLoadingPerm">
                        <td colspan="7" class="text-center py-5">
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
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="pFecha" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select id="pTipo" class="form-select">
                            <option value="salida_temprana">Salida Temprana</option>
                            <option value="llegada_tarde">Llegada Tarde</option>
                            <option value="dia_completo">Día Completo</option>
                            <option value="horas">Horas</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Horas de permiso <span class="text-danger">*</span></label>
                    <input type="number" id="pHoras" class="form-control" min="0" max="24" step="0.5" placeholder="Ej: 2">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo</label>
                    <textarea id="pMotivo" class="form-control" rows="2" placeholder="Descripción del permiso..."></textarea>
                </div>
                <div id="permisoError" class="alert alert-danger py-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="savePermiso()">Guardar</button>
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
const tipoLabels = {
    salida_temprana: 'Salida Temprana',
    llegada_tarde:   'Llegada Tarde',
    dia_completo:    'Día Completo',
    horas:           'Horas',
};
const estadoBadge = {
    pendiente: 'bg-warning text-dark',
    aprobado:  'bg-success',
    rechazado: 'bg-danger',
};
var tablaPermisos = null;

document.addEventListener('DOMContentLoaded', () => {
    const hoy   = new Date().toISOString().slice(0,10);
    const inicio = new Date(); inicio.setDate(1);
    document.getElementById('filterFrom').value = inicio.toISOString().slice(0,10);
    document.getElementById('filterTo').value   = hoy;
    loadEmpleados();
    loadPermisos();
});

async function loadEmpleados() {
    const res  = await fetch('/admin/empleados/list?per_page=500', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
    const data = await res.json();
    const empleados = data.data || [];
    const sel  = document.getElementById('filterEmpleado');
    const pSel = document.getElementById('pEmpleado');
    pSel.innerHTML = '<option value="">— Seleccionar —</option>';
    empleados.forEach(e => {
        const opt = `<option value="${e.id}">${e.name} (${e.codigo_empleado})</option>`;
        sel.innerHTML += opt;
        pSel.innerHTML += opt;
    });
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

    if (tablaPermisos) { tablaPermisos.processing(true); }

    try {
        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        const items = data.data || [];

        var trLoading = document.getElementById('trLoadingPerm');
        if (trLoading) trLoading.remove();

        if ($.fn.DataTable.isDataTable('#permisosTable')) {
            tablaPermisos.processing(false);
            tablaPermisos.clear().rows.add(items).draw();
        } else {
            tablaPermisos = $('#permisosTable').DataTable({
                data: items,
                processing: true,
                order: [[1, 'desc']],
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
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
                    processing: 'Procesando...',
                },
                initComplete: function() {
                    $('#permisosTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                    $('#permisosTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                    $('#permisosTable_filter').prepend(
                        '<button class="btn btn-sm btn-outline-secondary me-2" onclick="loadPermisos()" title="Recargar tabla"><i class="ti ti-refresh"></i></button>'
                    );
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
                        title: 'Fecha',
                        data: 'fecha',
                        render: function(data) { return data ? data.slice(0,10) : '—'; }
                    },
                    {
                        title: 'Tipo',
                        data: 'tipo',
                        render: function(data) { return tipoLabels[data] ?? data; }
                    },
                    {
                        title: 'Horas',
                        data: 'horas_permiso',
                        render: function(data) { return data + 'h'; }
                    },
                    {
                        title: 'Motivo',
                        data: 'motivo',
                        render: function(data) {
                            return '<small class="text-muted">' + (data || '—') + '</small>';
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
                                btns += '<button class="btn btn-sm btn-success me-1" onclick="aprobar(' + data + ')" title="Aprobar"><i class="fa-solid fa-check"></i></button>';
                                btns += '<button class="btn btn-sm btn-danger me-1" onclick="rechazar(' + data + ')" title="Rechazar"><i class="fa-solid fa-xmark"></i></button>';
                            }
                            btns += '<button class="btn btn-sm btn-outline-danger" onclick="eliminar(' + data + ')"><i class="fa-solid fa-trash"></i></button>';
                            return btns;
                        }
                    }
                ]
            });
        }
    } catch(e) {
        if (tablaPermisos) tablaPermisos.processing(false);
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
    document.getElementById('pFecha').value    = new Date().toISOString().slice(0,10);
    document.getElementById('pTipo').value     = 'salida_temprana';
    document.getElementById('pHoras').value    = '';
    document.getElementById('pMotivo').value   = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Permiso';
    document.getElementById('permisoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('permisoModal')).show();
}

async function savePermiso() {
    const userId = document.getElementById('pEmpleado').value;
    const fecha  = document.getElementById('pFecha').value;
    const horas  = document.getElementById('pHoras').value;
    if (!userId || !fecha || !horas) {
        const el = document.getElementById('permisoError');
        el.textContent = 'Empleado, fecha y horas son obligatorios.';
        el.style.display = 'block';
        return;
    }
    const payload = {
        user_id:       parseInt(userId),
        fecha,
        tipo:          document.getElementById('pTipo').value,
        horas_permiso: parseFloat(horas),
        motivo:        document.getElementById('pMotivo').value,
    };
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
}

async function aprobar(id) {
    if (!confirm('¿Aprobar este permiso?')) return;
    await fetch(`/admin/permisos/${id}/aprobar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadPermisos();
}

async function rechazar(id) {
    if (!confirm('¿Rechazar este permiso?')) return;
    await fetch(`/admin/permisos/${id}/rechazar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadPermisos();
}

async function eliminar(id) {
    if (!confirm('¿Eliminar este permiso?')) return;
    await fetch(`/admin/permisos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadPermisos();
}
</script>
@endpush
