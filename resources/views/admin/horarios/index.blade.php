@extends('layouts.admin')
@section('title', 'Horarios')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-clock me-2 text-primary"></i>Horarios</h4>
                <p class="text-muted mb-0">Turnos y jornadas laborales</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Horario
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 w-100" id="horariosTable">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Días laborales</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="horariosTbody">
                    <tr id="trLoadingHor">
                        <td colspan="4" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                            <p class="text-muted mt-2 mb-0 small">Cargando horarios...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="horarioModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="horarioId">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="hNombre" class="form-control" placeholder="Ej: Administrativo">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="hActivo" checked>
                            <label class="form-check-label">Activo</label>
                        </div>
                    </div>
                </div>

                <hr class="my-2">
                <p class="text-muted small mb-2">Define las horas para cada día. Deja los días no laborales sin marcar.</p>

                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:90px">Día</th>
                            <th style="width:70px" class="text-center">Laboral</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th style="width:105px">Almuerzo (min)</th>
                            <th style="width:105px">Retardo (min)</th>
                        </tr>
                    </thead>
                    <tbody id="diasTbody">
                        <!-- generado por JS -->
                    </tbody>
                </table>

                <div id="horarioError" class="alert alert-danger py-2 mt-3 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveHorario()">Guardar</button>
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
#horariosTable th, #horariosTable td {
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
const DIAS = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
var tablaHorarios = null;

function buildDiasRows(diasData = []) {
    const diasMap = {};
    diasData.forEach(d => { diasMap[d.dia_semana] = d; });

    return DIAS.map((nombre, i) => {
        const n   = i + 1;
        const d   = diasMap[n];
        const lab = !!d?.hora_entrada;
        const dis = lab ? '' : 'disabled';
        return `<tr id="dia-row-${n}">
            <td class="align-middle"><strong>${nombre}</strong></td>
            <td class="text-center align-middle">
                <input type="checkbox" class="form-check-input dia-check" data-dia="${n}" ${lab ? 'checked' : ''}
                    onchange="toggleDia(${n}, this.checked)">
            </td>
            <td>
                <input type="time" class="form-control form-control-sm dia-entrada" data-dia="${n}"
                    value="${d?.hora_entrada?.slice(0,5) || ''}" ${dis}>
            </td>
            <td>
                <input type="time" class="form-control form-control-sm dia-salida" data-dia="${n}"
                    value="${d?.hora_salida?.slice(0,5) || ''}" ${dis}>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm dia-almuerzo" data-dia="${n}"
                    min="0" max="240" placeholder="—" value="${d?.duracion_almuerzo_min ?? ''}" ${dis}>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm dia-retardo" data-dia="${n}"
                    min="0" max="120" value="${d?.retardo_min ?? 0}" ${dis}>
            </td>
        </tr>`;
    }).join('');
}

function toggleDia(n, checked) {
    ['dia-entrada','dia-salida','dia-almuerzo','dia-retardo'].forEach(cls => {
        document.querySelector(`.${cls}[data-dia="${n}"]`).disabled = !checked;
    });
}

async function loadHorarios() {
    try {
        const res  = await fetch('/admin/horarios/list');
        const data = await res.json();
        const items = data.data || [];

        var trLoading = document.getElementById('trLoadingHor');
        if (trLoading) trLoading.remove();

        if ($.fn.DataTable.isDataTable('#horariosTable')) {
            tablaHorarios.clear().rows.add(items).draw();
        } else {
            tablaHorarios = $('#horariosTable').DataTable({
                data: items,
                processing: true,
                order: [[0, 'asc']],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    lengthMenu: 'Mostrar _MENU_ registros',
                    zeroRecords: 'Sin horarios',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros)',
                    search: 'Buscar:',
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
                    processing: 'Procesando...',
                },
                initComplete: function() {
                    $('#horariosTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                    $('#horariosTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                    $('#horariosTable_filter').prepend(
                        '<button class="btn btn-sm btn-outline-secondary me-2" onclick="loadHorarios()" title="Recargar tabla"><i class="ti ti-refresh"></i></button>'
                    );
                },
                columns: [
                    {
                        title: 'Nombre', data: 'nombre',
                        render: function(d) { return '<strong>' + (d || '') + '</strong>'; }
                    },
                    {
                        title: 'Días laborales', data: 'dias', orderable: false,
                        render: function(d, type) {
                            var diasLab = (d || []).filter(function(x) { return x.hora_entrada; });
                            var nombres = diasLab.map(function(x) { return (DIAS[x.dia_semana - 1] || '').slice(0, 3); }).join(', ') || '—';
                            if (type !== 'display') return nombres;
                            return '<small class="text-muted">' + nombres + '</small>';
                        }
                    },
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
                            var h = JSON.stringify(row).replace(/'/g, '&#39;');
                            return '<button class="btn btn-sm btn-outline-primary me-1" onclick=\'editHorario(' + h + ')\'><i class="fa-solid fa-pen"></i></button>'
                                 + '<button class="btn btn-sm btn-outline-danger" onclick="deleteHorario(' + row.id + ')"><i class="fa-solid fa-trash"></i></button>';
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
    document.getElementById('horarioId').value   = data?.id || '';
    document.getElementById('hNombre').value   = data?.nombre || '';
    document.getElementById('hActivo').checked = data ? data.is_active : true;
    document.getElementById('modalTitle').textContent = data ? 'Editar Horario' : 'Nuevo Horario';
    document.getElementById('horarioError').style.display = 'none';
    document.getElementById('diasTbody').innerHTML = buildDiasRows(data?.dias || []);
    new bootstrap.Modal(document.getElementById('horarioModal')).show();
}

function editHorario(h) { openModal(h); }

async function saveHorario() {
    const id = document.getElementById('horarioId').value;
    const nombre = document.getElementById('hNombre').value.trim();
    if (!nombre) { showError('horarioError', 'El nombre es obligatorio.'); return; }

    const toTime = v => v ? v + ':00' : null;
    const dias = [];
    document.querySelectorAll('.dia-check').forEach(chk => {
        const n = parseInt(chk.dataset.dia);
        if (!chk.checked) return;
        const entrada  = document.querySelector(`.dia-entrada[data-dia="${n}"]`).value;
        const salida   = document.querySelector(`.dia-salida[data-dia="${n}"]`).value;
        const almuerzo = parseInt(document.querySelector(`.dia-almuerzo[data-dia="${n}"]`).value) || null;
        const retardo  = parseInt(document.querySelector(`.dia-retardo[data-dia="${n}"]`).value) || 0;
        if (entrada) {
            dias.push({ dia_semana: n, hora_entrada: toTime(entrada), hora_salida: toTime(salida), duracion_almuerzo_min: almuerzo, retardo_min: retardo });
        }
    });

    const payload = {
        nombre,
        is_active: document.getElementById('hActivo').checked,
        dias,
    };
    const url    = id ? `/admin/horarios/${id}` : '/admin/horarios';
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(payload),
    });
    if (res.ok) {
        bootstrap.Modal.getInstance(document.getElementById('horarioModal')).hide();
        loadHorarios();
    } else {
        const err = await res.json();
        showError('horarioError', Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
    }
}

async function deleteHorario(id) {
    if (!confirm('¿Desactivar este horario?')) return;
    await fetch(`/admin/horarios/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
    loadHorarios();
}

function showError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg; el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => loadHorarios());
</script>
@endpush
