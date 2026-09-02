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
                <div class="card-body p-2">
                    <div class="row g-2 mb-2 align-items-end">
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Buscar..."
                                onkeydown="if(event.key==='Enter') cargarTabla()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="filterDesde">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="filterHasta">
                        </div>
                        <div class="col-md-auto d-flex align-items-end gap-2">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosExtraVis" title="Más filtros">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="cargarTabla()">
                                <i class="ti ti-search me-1"></i> Filtrar
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="clearFilters()">
                                <i class="ti ti-x me-1"></i> Limpiar
                            </button>
                            <button class="btn btn-sm btn-success" onclick="exportarExcel()">
                                <i class="ti ti-file-spreadsheet me-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                    <div class="collapse" id="filtrosExtraVis">
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filterSede">
                                    <option value="">Todas las sedes</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select form-select-sm" id="filterEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="en_sede">En sede</option>
                                    <option value="salieron">Con salida</option>
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
                    <table class="table table-hover mb-0 w-100" id="visitantesTable">
                        <thead class="table-light"></thead>
                        <tbody></tbody>
                    </table>
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
                            <button class="btn btn-outline-secondary" type="button" id="btnBuscarCedula" onclick="buscarPorCedula()">
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

{{-- Modal Editar Visitante --}}
<div class="modal fade" id="modalEditarVisitante" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-edit me-2"></i>Editar registro de visitante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editarAlert" class="d-none mb-3"></div>
                <input type="hidden" id="edit_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Cédula <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="edit_cedula">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="edit_nombre">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Teléfono</label>
                        <input type="text" class="form-control form-control-sm" id="edit_telefono">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Empresa</label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="edit_empresa">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">EPS</label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="edit_eps">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">ARL</label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="edit_arl">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Placa del vehículo</label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="edit_placa">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">¿A quién visita? <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rm-upper" id="edit_persona_visita">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Fecha y hora de entrada <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control form-control-sm" id="edit_hora_entrada">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm fw-semibold">Fecha y hora de salida</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="edit_hora_salida">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardarEdicion" onclick="guardarEdicion()">
                    <i class="ti ti-check me-1"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Log --}}
<div class="modal fade" id="modalLog" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-history me-2"></i>Historial de cambios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="logSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="logContent" class="d-none">
                    <div class="card border mb-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="logTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:130px">Fecha</th>
                                            <th style="width:80px">Evento</th>
                                            <th style="width:140px">Usuario</th>
                                            <th>Campo</th>
                                            <th>Anterior</th>
                                            <th>Nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logTbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="logVacio" class="text-center text-muted py-4 d-none">Sin registros de cambios.</div>
                </div>
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

{{-- Botón flotante para visibilidad de columnas --}}
<div id="colVisBtn" title="Mostrar / ocultar columnas"
     style="position:fixed;bottom:70px;right:28px;z-index:1055;cursor:pointer;
            width:48px;height:48px;border-radius:50%;background:#1ab394;
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 4px 14px rgba(0,0,0,.25);transition:background .2s;"
     onmouseenter="this.style.background='#17a07d'" onmouseleave="this.style.background='#1ab394'"
     onclick="toggleColVisPanel()">
    <i class="ti ti-settings text-white" style="font-size:22px;"></i>
</div>

<div id="colVisPanel"
     style="display:none;position:fixed;bottom:128px;right:28px;z-index:1056;
            background:#fff;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.18);
            min-width:220px;padding:14px 16px;">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="fw-semibold small">Columnas visibles</span>
        <button class="btn btn-link btn-sm p-0 text-muted" onclick="toggleColVisPanel()">
            <i class="ti ti-x"></i>
        </button>
    </div>
    <div id="colVisChecks"></div>
    <div class="mt-2 pt-2 border-top d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="colVisTodos(true)">Todos</button>
        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="colVisTodos(false)">Ninguno</button>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
.rm-upper { text-transform: uppercase; }
.rm-upper::placeholder { text-transform: none; }

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
#visitantesTable th, #visitantesTable td {
    font-size: 13px;
    vertical-align: middle;
    white-space: nowrap;
}
#visitantesTable td:nth-child(14) { white-space: normal; } /* Observación */
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
var csrfToken = '{{ csrf_token() }}';
var sedesData = @json($sedes);
var tabla = null;
var visitantesData = [];
var dataLoadedAt = null;

function localDateStr() {
    var d = new Date();
    return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

function formatDT(dt) {
    if (!dt) return '—';
    var d = new Date(dt);
    return String(d.getDate()).padStart(2,'0') + '/' +
           String(d.getMonth()+1).padStart(2,'0') + '/' +
           d.getFullYear() + ' ' +
           String(d.getHours()).padStart(2,'0') + ':' +
           String(d.getMinutes()).padStart(2,'0');
}

function formatMins(mins) {
    if (mins < 1) return '< 1m';
    var h = Math.floor(mins / 60), m = mins % 60;
    return h > 0 ? h + 'h ' + m + 'm' : m + 'm';
}

function cargarTabla() {
    var params = new URLSearchParams({
        sede_id:  $('#filterSede').val(),
        desde:    $('#filterDesde').val(),
        hasta:    $('#filterHasta').val(),
        search:   $('#filterSearch').val(),
        estado:   $('#filterEstado').val(),
        per_page: 5000
    });

    $.getJSON('/admin/visitantes/list?' + params, function(json) {
        var datos = json.data || [];
        visitantesData = datos;
        dataLoadedAt = new Date();

        if ($.fn.DataTable.isDataTable('#visitantesTable')) {
            tabla.clear().rows.add(datos).draw();
        } else {
            tabla = $('#visitantesTable').DataTable({
                data: datos,
                order: [[8, 'desc']],
                scrollX: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                },
                initComplete: function() {
                    $('#visitantesTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                    $('#visitantesTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                },
                columns: [
                    {
                        title: 'Cédula',
                        data: 'cedula'
                    },
                    {
                        title: 'Nombre',
                        data: 'nombre',
                        render: function(data) {
                            return '<strong>' + (data || '—') + '</strong>';
                        }
                    },
                    {
                        title: 'Empresa',
                        data: 'empresa',
                        defaultContent: '—'
                    },
                    {
                        title: 'Teléfono',
                        data: 'telefono',
                        defaultContent: '—'
                    },
                    {
                        title: 'EPS / ARL',
                        data: 'eps',
                        render: function(data, type, row) {
                            return '<small>' + (row.eps || '—') + '<br>' + (row.arl || '—') + '</small>';
                        }
                    },
                    {
                        title: 'Placa',
                        data: 'placa',
                        render: function(data) {
                            return data ? '<span class="badge bg-secondary">' + data + '</span>' : '—';
                        }
                    },
                    {
                        title: 'Visita a',
                        data: 'persona_visita',
                        defaultContent: '—'
                    },
                    {
                        title: 'Sede',
                        data: 'sede',
                        render: function(data) {
                            return data ? '<span class="badge bg-primary">' + data.nombre + '</span>' : '—';
                        }
                    },
                    {
                        title: 'Entrada',
                        data: 'hora_entrada',
                        render: function(data) {
                            if (!data) return '—';
                            var d = new Date(data);
                            var fecha = String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
                            var hora  = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
                            return '<small>' + fecha + '<br><span class="text-muted">Hora: ' + hora + '</span></small>';
                        }
                    },
                    {
                        title: 'Salida',
                        data: 'hora_salida',
                        render: function(data) {
                            if (!data) return '<span class="badge bg-warning text-dark">En sede</span>';
                            var d = new Date(data);
                            var fecha = String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
                            var hora  = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
                            return '<small>' + fecha + '<br><span class="text-muted">Hora: ' + hora + '</span></small>';
                        }
                    },
                    {
                        title: 'Tiempo en sede',
                        data: 'minutos_en_sede',
                        render: function(data, type, row) {
                            if (data == null) return '—';
                            var txt = formatMins(data);
                            if (!row.hora_salida) {
                                return '<span class="text-warning fw-semibold" data-minutos="' + data + '">\u23f3 ' + txt + '</span>';
                            }
                            return txt;
                        }
                    },
                    {
                        title: 'Inducción',
                        data: 'induccion_requerida',
                        orderable: false,
                        render: function(data, type, row) {
                            if (!data) return '<span class="badge bg-secondary">No requerida</span>';
                            if (row.induccion_fecha) {
                                return '<div>' +
                                    '<span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Realizada</span>' +
                                    '<small class="d-block text-muted mt-1">' + formatDT(row.induccion_fecha) + '</small>' +
                                    '<button class="btn btn-link btn-sm p-0 mt-1 text-secondary" style="font-size:11px" onclick="marcarInduccion(' + row.id + ')">' +
                                        '<i class="ti ti-calendar-edit me-1"></i>Cambiar fecha' +
                                    '</button></div>';
                            }
                            return '<div class="d-flex flex-column align-items-start gap-1">' +
                                '<span class="badge bg-danger"><i class="ti ti-alert-circle me-1"></i>Pendiente</span>' +
                                '<button class="btn btn-sm btn-success py-0 px-2 mt-1" onclick="marcarInduccion(' + row.id + ')">' +
                                    '<i class="ti ti-calendar-plus me-1"></i>Registrar fecha' +
                                '</button></div>';
                        }
                    },
                    {
                        title: 'Última inducción',
                        data: 'ultima_induccion_fecha',
                        orderable: false,
                        render: function(data, type, row) {
                            if (!data) return '<span class="text-muted small">Sin registro</span>';
                            var partes = String(data).split(' ');
                            var fecha = partes[0] || data;
                            var hora  = partes[1] || '';
                            return '<small class="fw-semibold">' + fecha + '</small>' +
                                (hora ? '<br><span class="text-muted small">Hora: ' + hora + '</span>' : '');
                        }
                    },
                    {
                        title: 'Observación',
                        data: 'observacion',
                        orderable: false,
                        render: function(data, type, row) {
                            return '<div style="max-width:160px">' +
                                (data ? '<small class="text-muted d-block mb-1" style="word-break:break-word">' + data + '</small>' : '') +
                                '<button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="editarObservacion(' + row.id + ')">' +
                                    '<i class="ti ti-' + (data ? 'edit' : 'plus') + '"></i>' +
                                '</button></div>';
                        }
                    },
                    {
                        title: 'Foto',
                        data: 'imagen_entrada',
                        orderable: false,
                        render: function(data, type, row) {
                            return data
                                ? '<button class="btn btn-sm btn-outline-primary" onclick="verFoto(' + row.id + ')"><i class="ti ti-photo"></i></button>'
                                : '—';
                        }
                    },
                    {
                        title: '',
                        data: 'hora_salida',
                        orderable: false,
                        render: function(data, type, row) {
                            var btnSalida = data ? '' :
                                '<button class="btn btn-sm btn-outline-danger me-1" onclick="forzarSalida(' + row.id + ')" title="Registrar salida">' +
                                    '<i class="ti ti-door-exit"></i>' +
                                '</button>';
                            var btnEditar =
                                '<button class="btn btn-sm btn-outline-secondary me-1" onclick="abrirEdicion(' + row.id + ')" title="Editar">' +
                                    '<i class="ti ti-edit"></i>' +
                                '</button>';
                            var btnLog =
                                '<button class="btn btn-sm btn-outline-info" onclick="verLog(' + row.id + ')" title="Ver historial">' +
                                    '<i class="ti ti-history"></i>' +
                                '</button>';
                            return btnSalida + btnEditar + btnLog;
                        }
                    }
                ]
            });
        }
    });
}

function clearFilters() {
    $('#filterSede').val('');
    $('#filterSearch').val('');
    $('#filterEstado').val('');
    $('#filterDesde').val(localDateStr());
    $('#filterHasta').val(localDateStr());
    cargarTabla();
}

function exportarExcel() {
    var params = new URLSearchParams({
        sede_id: $('#filterSede').val(),
        desde:   $('#filterDesde').val(),
        hasta:   $('#filterHasta').val(),
        search:  $('#filterSearch').val(),
        estado:  $('#filterEstado').val(),
        export:  'xlsx'
    });
    window.location.href = '/admin/visitantes/list?' + params;
}

async function marcarInduccion(id) {
    var v = visitantesData.find(function(x) { return x.id === id; });
    var fechaActual = v && v.induccion_fecha
        ? new Date(v.induccion_fecha).toISOString().slice(0, 16)
        : new Date(new Date() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    var result = await Swal.fire({
        title: '<i class="ti ti-shield-check me-2 text-success"></i>Fecha de inducción',
        html: '<p class="text-muted small mb-3">Ingresa la fecha y hora en que se realizó la inducción.</p>' +
              '<input id="swal-fecha-ind" type="datetime-local" value="' + fechaActual + '" class="form-control">',
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-check me-1"></i>Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        width: 420,
        customClass: { htmlContainer: 'text-start' },
        preConfirm: function() {
            var val = document.getElementById('swal-fecha-ind').value;
            if (!val) { Swal.showValidationMessage('Selecciona una fecha'); return false; }
            return val;
        },
        didOpen: function() { document.getElementById('swal-fecha-ind').focus(); }
    });
    if (!result.isConfirmed) return;
    await fetch('/admin/visitantes/' + id + '/induccion', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        body: JSON.stringify({ fecha: result.value })
    });
    cargarTabla();
}

async function editarObservacion(id) {
    var v = visitantesData.find(function(x) { return x.id === id; });
    var obsActual = v ? (v.observacion || '') : '';
    var result = await Swal.fire({
        title: '<i class="ti ti-notes me-2 text-primary"></i>Observación',
        html: '<p class="text-muted small mb-3">Escribe una nota o comentario.</p>' +
              '<textarea id="swal-obs" class="form-control" rows="4" placeholder="Escribe una observación...">' + obsActual + '</textarea>',
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-check me-1"></i>Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4F46E5',
        cancelButtonColor: '#6c757d',
        width: 480,
        customClass: { htmlContainer: 'text-start' },
        preConfirm: function() { return document.getElementById('swal-obs').value.trim(); },
        didOpen: function() {
            var ta = document.getElementById('swal-obs');
            ta.focus();
            ta.setSelectionRange(ta.value.length, ta.value.length);
        }
    });
    if (!result.isConfirmed) return;
    await fetch('/admin/visitantes/' + id + '/observacion', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        body: JSON.stringify({ observacion: result.value || null })
    });
    cargarTabla();
}

async function forzarSalida(id) {
    // Fecha/hora actual como valor por defecto
    var now = new Date();
    var pad = n => String(n).padStart(2, '0');
    var defaultDt = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) +
                    'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());

    var result = await Swal.fire({
        title: '¿Registrar salida?',
        icon: 'question',
        html:
            '<p class="text-muted small mb-3">Confirma la fecha y hora de salida del visitante.</p>' +
            '<label class="form-label fw-semibold small w-100 text-start">Fecha y hora de salida</label>' +
            '<input id="swal-hora-salida" type="datetime-local" class="form-control form-control-sm" value="' + defaultDt + '">',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4F46E5',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            var val = document.getElementById('swal-hora-salida').value;
            if (!val) { Swal.showValidationMessage('Debes indicar la fecha y hora de salida'); return false; }
            return val;
        }
    });
    if (!result.isConfirmed) return;
    await fetch('/admin/visitantes/' + id + '/forzar-salida', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        body: JSON.stringify({ hora_salida: result.value })
    });
    cargarTabla();
}

async function verFoto(visitanteId) {
    var spinner = document.getElementById('fotoSpinner');
    var content = document.getElementById('fotoContent');
    spinner.classList.remove('d-none');
    content.classList.add('d-none');
    new bootstrap.Modal(document.getElementById('fotoModal')).show();
    var res = await fetch('/admin/visitantes/' + visitanteId + '/foto');
    var data = await res.json();
    var imgE = document.getElementById('fotoEntrada'), imgS = document.getElementById('fotoSalida');
    var vacE = document.getElementById('fotoEntradaVacio'), vacS = document.getElementById('fotoSalidaVacio');
    if (data.entrada) { imgE.src = data.entrada; imgE.classList.remove('d-none'); vacE.classList.add('d-none'); }
    else { imgE.classList.add('d-none'); vacE.classList.remove('d-none'); }
    if (data.salida)  { imgS.src = data.salida;  imgS.classList.remove('d-none'); vacS.classList.add('d-none'); }
    else { imgS.classList.add('d-none'); vacS.classList.remove('d-none'); }
    spinner.classList.add('d-none');
    content.classList.remove('d-none');
}

// Actualiza contadores en vivo cada 30s
setInterval(function() {
    if (!dataLoadedAt) return;
    var elapsedMins = Math.floor((new Date() - dataLoadedAt) / 60000);
    $('[data-minutos]').each(function() {
        var base = parseInt($(this).attr('data-minutos'), 10);
        $(this).text('\u23f3 ' + formatMins(base + elapsedMins));
    });
}, 30000);

var LOG_LABELS = {
    nombre: 'Nombre', cedula: 'Cédula', telefono: 'Teléfono', empresa: 'Empresa',
    eps: 'EPS', arl: 'ARL', placa: 'Placa', persona_visita: 'Visita a',
    hora_entrada: 'Entrada', hora_salida: 'Salida',
    induccion_requerida: 'Inducción req.', induccion_fecha: 'Fecha inducción',
    observacion: 'Observación', sede_id: 'Sede', user_id: 'Usuario reg.'
};

async function verLog(id) {
    var spinner = document.getElementById('logSpinner');
    var content = document.getElementById('logContent');
    var tbody   = document.getElementById('logTbody');
    var vacio   = document.getElementById('logVacio');

    spinner.classList.remove('d-none');
    content.classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalLog')).show();

    var res  = await fetch('/admin/visitantes/' + id + '/log');
    var logs = await res.json();

    spinner.classList.add('d-none');
    content.classList.remove('d-none');

    if (!logs.length) {
        tbody.innerHTML = '';
        vacio.classList.remove('d-none');
        return;
    }
    vacio.classList.add('d-none');

    var rows = [];
    logs.forEach(function(log) {
        var badgeClass = log.evento === 'DELETE' ? 'bg-danger' : 'bg-warning text-dark';
        var anterior = log.anterior || {};
        var nuevo    = log.nuevo    || {};
        var campos   = Object.keys(Object.assign({}, anterior, nuevo));
        var cambios  = campos.filter(function(k) {
            return JSON.stringify(anterior[k]) !== JSON.stringify(nuevo[k]);
        });

        if (!cambios.length) cambios = campos;

        cambios.forEach(function(campo, i) {
            rows.push(
                '<tr>' +
                (i === 0
                    ? '<td rowspan="' + cambios.length + '" class="align-middle small text-muted">' + (log.created_at || '') + '</td>' +
                      '<td rowspan="' + cambios.length + '" class="align-middle"><span class="badge ' + badgeClass + '">' + log.evento + '</span></td>' +
                      '<td rowspan="' + cambios.length + '" class="align-middle small">' + log.usuario + '</td>'
                    : '') +
                '<td class="small fw-semibold">' + (LOG_LABELS[campo] || campo) + '</td>' +
                '<td class="small text-danger">' + (anterior[campo] != null ? anterior[campo] : '—') + '</td>' +
                '<td class="small text-success">' + (nuevo[campo]    != null ? nuevo[campo]    : '—') + '</td>' +
                '</tr>'
            );
        });
    });

    tbody.innerHTML = rows.join('');
}

function toLocalDatetimeInput(dt) {
    if (!dt) return '';
    var d = new Date(dt);
    return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
}

function abrirEdicion(id) {
    var v = visitantesData.find(function(x) { return x.id === id; });
    if (!v) return;
    document.getElementById('edit_id').value             = v.id;
    document.getElementById('edit_cedula').value         = v.cedula || '';
    document.getElementById('edit_nombre').value         = v.nombre || '';
    document.getElementById('edit_telefono').value       = v.telefono || '';
    document.getElementById('edit_empresa').value        = v.empresa || '';
    document.getElementById('edit_eps').value            = v.eps || '';
    document.getElementById('edit_arl').value            = v.arl || '';
    document.getElementById('edit_placa').value          = v.placa || '';
    document.getElementById('edit_persona_visita').value = v.persona_visita || '';
    document.getElementById('edit_hora_entrada').value   = toLocalDatetimeInput(v.hora_entrada);
    document.getElementById('edit_hora_salida').value    = toLocalDatetimeInput(v.hora_salida);
    document.getElementById('editarAlert').className = 'd-none mb-3';
    new bootstrap.Modal(document.getElementById('modalEditarVisitante')).show();
}

async function guardarEdicion() {
    var btn = document.getElementById('btnGuardarEdicion');
    var alertEl = document.getElementById('editarAlert');
    var id = document.getElementById('edit_id').value;
    var payload = {
        cedula:         document.getElementById('edit_cedula').value.trim(),
        nombre:         document.getElementById('edit_nombre').value.trim(),
        telefono:       document.getElementById('edit_telefono').value.trim() || null,
        empresa:        document.getElementById('edit_empresa').value.trim() || null,
        eps:            document.getElementById('edit_eps').value.trim() || null,
        arl:            document.getElementById('edit_arl').value.trim() || null,
        placa:          document.getElementById('edit_placa').value.trim().toUpperCase() || null,
        persona_visita: document.getElementById('edit_persona_visita').value.trim(),
        hora_entrada:   document.getElementById('edit_hora_entrada').value,
        hora_salida:    document.getElementById('edit_hora_salida').value || null,
    };
    if (!payload.cedula || !payload.nombre || !payload.persona_visita || !payload.hora_entrada) {
        alertEl.className = 'alert alert-warning mb-3';
        alertEl.textContent = 'Completa los campos obligatorios.';
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
    try {
        var res = await fetch('/admin/visitantes/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload)
        });
        var data = await res.json();
        if (res.ok && data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalEditarVisitante')).hide();
            cargarTabla();
        } else {
            alertEl.className = 'alert alert-danger mb-3';
            alertEl.textContent = data.message || 'Error al guardar.';
        }
    } catch(e) {
        alertEl.className = 'alert alert-danger mb-3';
        alertEl.textContent = 'Error de conexión.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Guardar cambios';
    }
}

async function buscarPorCedula() {
    var cedula = $('#rm_cedula').val().trim();
    var msg = document.getElementById('rm_cedula_msg');
    var btn = document.getElementById('btnBuscarCedula');
    if (!cedula) { msg.className = 'form-text text-warning'; msg.textContent = 'Ingresa una cédula primero.'; msg.classList.remove('d-none'); return; }
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; msg.classList.add('d-none');
    try {
        var res = await fetch('/admin/visitantes/buscar-cedula?cedula=' + encodeURIComponent(cedula));
        var data = await res.json();
        if (data.found) {
            var d = data.data;
            $('#rm_nombre').val(d.nombre||''); $('#rm_telefono').val(d.telefono||''); $('#rm_empresa').val(d.empresa||''); $('#rm_eps').val(d.eps||''); $('#rm_arl').val(d.arl||'');
            msg.className = 'form-text text-success'; msg.textContent = '\u2713 Datos cargados. Puedes editarlos.';
        } else { msg.className = 'form-text text-muted'; msg.textContent = 'No se encontraron registros anteriores.'; }
    } catch(e) { msg.className = 'form-text text-danger'; msg.textContent = 'Error al buscar.'; }
    finally { msg.classList.remove('d-none'); btn.disabled = false; btn.innerHTML = '<i class="ti ti-search"></i>'; }
}

function abrirRegistroManual() {
    ['rm_sede_id','rm_cedula','rm_nombre','rm_telefono','rm_empresa','rm_eps','rm_arl','rm_placa','rm_persona_visita'].forEach(function(id) {
        var el = document.getElementById(id); if (el) el.value = '';
    });
    var now = new Date(); now.setSeconds(0,0);
    document.getElementById('rm_hora_entrada').value = new Date(now.getTime() - now.getTimezoneOffset()*60000).toISOString().slice(0,16);
    document.getElementById('registroManualAlert').className = 'd-none mb-3';
    document.getElementById('rm_cedula_msg').className = 'form-text d-none';
    new bootstrap.Modal(document.getElementById('modalRegistroManual')).show();
}

async function guardarVisitanteManual() {
    var btn = document.getElementById('btnGuardarVisitante');
    var alertEl = document.getElementById('registroManualAlert');
    var payload = {
        sede_id: $('#rm_sede_id').val(), cedula: $('#rm_cedula').val().trim(), nombre: $('#rm_nombre').val().trim(),
        telefono: $('#rm_telefono').val().trim(), empresa: $('#rm_empresa').val().trim(),
        eps: $('#rm_eps').val().trim(), arl: $('#rm_arl').val().trim(),
        placa: $('#rm_placa').val().trim().toUpperCase() || null,
        persona_visita: $('#rm_persona_visita').val().trim(),
        hora_entrada: $('#rm_hora_entrada').val() || null
    };
    var requeridos = ['sede_id','cedula','nombre','telefono','empresa','eps','arl','persona_visita'];
    if (requeridos.find(function(k) { return !payload[k]; })) {
        alertEl.className = 'alert alert-warning mb-3'; alertEl.textContent = 'Completa todos los campos obligatorios.'; return;
    }
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
    try {
        var res = await fetch('{{ route("admin.visitantes.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload)
        });
        var data = await res.json();
        if (res.ok && data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalRegistroManual')).hide();
            cargarTabla();
        } else { alertEl.className = 'alert alert-danger mb-3'; alertEl.textContent = data.message || 'Error al guardar.'; }
    } catch(e) { alertEl.className = 'alert alert-danger mb-3'; alertEl.textContent = 'Error de conexión.'; }
    finally { btn.disabled = false; btn.innerHTML = '<i class="ti ti-check me-1"></i> Registrar entrada'; }
}

$(document).ready(function() {
    // Cargar sedes
    sedesData.forEach(function(s) {
        $('#filterSede').append('<option value="' + s.id + '">' + s.nombre + '</option>');
    });
    // Fechas por defecto
    $('#filterDesde').val(localDateStr());
    $('#filterHasta').val(localDateStr());
    // Cargar tabla
    cargarTabla();
    // Mayúsculas
    $('.rm-upper').on('input', function() {
        var pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
    // Cerrar panel al hacer click fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#colVisPanel, #colVisBtn').length) {
            $('#colVisPanel').hide();
        }
    });
});

// ── Visibilidad de columnas ──────────────────────────────────────────────────
var COL_VIS_KEY = 'visitantes_col_vis';

var COL_VIS_DEFS = [
    { idx: 0,  label: 'Cédula',          default: true  },
    { idx: 1,  label: 'Nombre',          default: true  },
    { idx: 2,  label: 'Empresa',         default: true  },
    { idx: 3,  label: 'Teléfono',        default: false },
    { idx: 4,  label: 'EPS / ARL',       default: false },
    { idx: 5,  label: 'Placa',           default: false },
    { idx: 6,  label: 'Visita a',        default: true  },
    { idx: 7,  label: 'Sede',            default: true  },
    { idx: 8,  label: 'Entrada',         default: true  },
    { idx: 9,  label: 'Salida',          default: true  },
    { idx: 10, label: 'Tiempo en sede',  default: true  },
    { idx: 11, label: 'Inducción',       default: true  },
    { idx: 12, label: 'Última inducción',default: false },
    { idx: 13, label: 'Observación',     default: true  },
    { idx: 14, label: 'Foto',            default: true  },
    { idx: 15, label: 'Acciones',        default: true  },
];

function colVisGetState() {
    try {
        var stored = localStorage.getItem(COL_VIS_KEY);
        if (stored) return JSON.parse(stored);
    } catch(e) {}
    // Estado por defecto
    var state = {};
    COL_VIS_DEFS.forEach(function(c) { state[c.idx] = c.default; });
    return state;
}

function colVisSaveState(state) {
    localStorage.setItem(COL_VIS_KEY, JSON.stringify(state));
}

function colVisApply(state) {
    if (!tabla) return;
    COL_VIS_DEFS.forEach(function(c) {
        tabla.column(c.idx).visible(!!state[c.idx], false);
    });
    tabla.columns.adjust().draw(false);
}

function colVisBuildPanel() {
    var state = colVisGetState();
    var container = document.getElementById('colVisChecks');
    container.innerHTML = '';
    COL_VIS_DEFS.forEach(function(c) {
        var checked = state[c.idx] ? 'checked' : '';
        var div = document.createElement('div');
        div.className = 'form-check form-switch mb-1';
        div.innerHTML =
            '<input class="form-check-input" type="checkbox" id="colvis_' + c.idx + '" ' + checked + ' onchange="colVisToggle(' + c.idx + ', this.checked)">' +
            '<label class="form-check-label small" for="colvis_' + c.idx + '">' + c.label + '</label>';
        container.appendChild(div);
    });
}

function colVisToggle(idx, visible) {
    var state = colVisGetState();
    state[idx] = visible;
    colVisSaveState(state);
    colVisApply(state);
}

function colVisTodos(visible) {
    var state = colVisGetState();
    COL_VIS_DEFS.forEach(function(c) { state[c.idx] = visible; });
    colVisSaveState(state);
    colVisApply(state);
    // Actualizar checkboxes sin cerrar el panel
    COL_VIS_DEFS.forEach(function(c) {
        var el = document.getElementById('colvis_' + c.idx);
        if (el) el.checked = visible;
    });
}

function toggleColVisPanel() {
    var panel = document.getElementById('colVisPanel');
    if (panel.style.display === 'none') {
        colVisBuildPanel();
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

// Aplicar visibilidad guardada una vez la tabla esté lista
var _colVisInterval = setInterval(function() {
    if (tabla) {
        colVisApply(colVisGetState());
        clearInterval(_colVisInterval);
    }
}, 200);
</script>
@endpush
