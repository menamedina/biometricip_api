@extends('layouts.admin')
@section('title', 'Festivos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-calendar-xmark me-2 text-primary"></i>Festivos</h4>
                <p class="text-muted mb-0">Días no laborables</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Festivo
            </button>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body p-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-auto">
                    <label class="form-label form-label-sm mb-1">Año</label>
                    <select id="filterYear" class="form-select form-select-sm w-auto">
                        <option value="">Todos los años</option>
                    </select>
                </div>
                <div class="col-md-auto d-flex align-items-end gap-2">
                    <button class="btn btn-sm btn-primary" onclick="loadFestivos()">
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
            <table class="table table-hover mb-0 w-100" id="festivosTable">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody id="festivosTbody">
                    <tr id="trLoadingFest">
                        <td colspan="4" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                            <p class="text-muted mt-2 mb-0 small">Cargando festivos...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="festivoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Festivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="festivoId">
                <div class="mb-3">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" id="fFecha" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="fNombre" class="form-control" placeholder="Ej: Día de la Independencia">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="fActivo" checked>
                    <label class="form-check-label">Activo</label>
                </div>
                <div id="festivoError" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveFestivo()">Guardar</button>
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
#festivosTable th, #festivosTable td {
    font-size: 13px;
    vertical-align: middle;
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
var tablaFestivos = null;

// Llenar select de años
document.addEventListener('DOMContentLoaded', () => {
    const currentYear = new Date().getFullYear();
    const selYear = document.getElementById('filterYear');
    for (let y = currentYear + 1; y >= currentYear - 2; y--) {
        const opt = document.createElement('option');
        opt.value = y; opt.textContent = y;
        if (y === currentYear) opt.selected = true;
        selYear.appendChild(opt);
    }
    loadFestivos();
});

function limpiarFiltros() {
    document.getElementById('filterYear').value = new Date().getFullYear();
    loadFestivos();
}

async function loadFestivos() {
    const year = document.getElementById('filterYear').value;
    let url = '/admin/festivos/list';
    if (year) url += `?year=${year}`;

    try {
        const res  = await fetch(url);
        const data = await res.json();
        const items = data.data || [];

        var trLoading = document.getElementById('trLoadingFest');
        if (trLoading) trLoading.remove();

        if ($.fn.DataTable.isDataTable('#festivosTable')) {
            tablaFestivos.clear().rows.add(items).draw();
        } else {
            tablaFestivos = $('#festivosTable').DataTable({
                data: items,
                processing: true,
                order: [[0, 'asc']],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    lengthMenu: 'Mostrar _MENU_ registros',
                    zeroRecords: 'Sin festivos',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros)',
                    search: 'Buscar:',
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
                    processing: 'Procesando...',
                },
                initComplete: function() {
                    $('#festivosTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                    $('#festivosTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                    $('#festivosTable_filter').prepend(
                        '<button class="btn btn-sm btn-outline-secondary me-2" onclick="loadFestivos()" title="Recargar tabla"><i class="ti ti-refresh"></i></button>'
                    );
                },
                columns: [
                    {
                        title: 'Fecha', data: 'fecha',
                        render: function(d, type) {
                            var iso = (d || '').slice(0, 10);
                            if (type !== 'display') return iso;
                            return '<strong>' + iso.split('-').reverse().join('/') + '</strong>';
                        }
                    },
                    { title: 'Nombre', data: 'nombre', render: function(d) { return d || '—'; } },
                    {
                        title: 'Estado', data: 'is_active',
                        render: function(d, type) {
                            if (type !== 'display') return d ? 1 : 0;
                            return '<span class="badge ' + (d ? 'bg-success' : 'bg-secondary') + '">' + (d ? 'Activo' : 'Inactivo') + '</span>';
                        }
                    },
                    {
                        title: 'Acciones', data: null, orderable: false,
                        render: function(d, type, row) {
                            var f = JSON.stringify(row).replace(/'/g, '&#39;');
                            return '<button class="btn btn-sm btn-outline-primary me-1" onclick=\'editFestivo(' + f + ')\'><i class="fa-solid fa-pen"></i></button>'
                                 + '<button class="btn btn-sm btn-outline-danger" onclick="deleteFestivo(' + row.id + ')"><i class="fa-solid fa-trash"></i></button>';
                        }
                    }
                ]
            });
        }
    } catch(e) {
        console.error(e);
    }
}

function openModal(data = null) {
    document.getElementById('festivoId').value  = data?.id || '';
    document.getElementById('fFecha').value     = data?.fecha || '';
    document.getElementById('fNombre').value    = data?.nombre || '';
    document.getElementById('fActivo').checked  = data ? data.is_active : true;
    document.getElementById('modalTitle').textContent = data ? 'Editar Festivo' : 'Nuevo Festivo';
    document.getElementById('festivoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('festivoModal')).show();
}

function editFestivo(f) { openModal(f); }

async function saveFestivo() {
    const id = document.getElementById('festivoId').value;
    const fecha  = document.getElementById('fFecha').value;
    const nombre = document.getElementById('fNombre').value.trim();
    if (!fecha || !nombre) { showError('festivoError', 'Fecha y nombre son obligatorios.'); return; }

    const payload = { fecha, nombre, is_active: document.getElementById('fActivo').checked };
    const url    = id ? `/admin/festivos/${id}` : '/admin/festivos';
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(payload),
    });
    if (res.ok) {
        bootstrap.Modal.getInstance(document.getElementById('festivoModal')).hide();
        loadFestivos();
    } else {
        const err = await res.json();
        showError('festivoError', Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
    }
}

async function deleteFestivo(id) {
    if (!confirm('¿Eliminar este festivo?')) return;
    await fetch(`/admin/festivos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadFestivos();
}

function showError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg; el.style.display = 'block';
}

</script>
@endpush
