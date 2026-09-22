@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Capacitaciones</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Capacitaciones</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-school me-2 text-primary"></i>Gestión de Capacitaciones
                    </h5>
                    @can('capacitaciones.crear')
                    <button class="btn btn-primary btn-sm" onclick="abrirModalCrear()">
                        <i class="ti ti-plus me-1"></i> Nueva Capacitación
                    </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaCapacitaciones" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Instructor</th>
                                    <th>Fecha</th>
                                    <th>Expira</th>
                                    <th>Estado</th>
                                    <th>Asistentes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL CREAR CAPACITACIÓN
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearLabel">
                    <i class="ti ti-school me-2 text-primary"></i>Nueva Capacitación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                        <input type="text" id="crearTitulo" class="form-control" placeholder="Ej: Seguridad en Redes" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Tipo de evento <span class="text-danger">*</span></label>
                        <select id="crearTipoEvento" class="form-select" required>
                            <option value="" selected disabled>Selecciona el tipo de evento</option>
                            <option value="capacitacion">Capacitación</option>
                            <option value="asistencia">Registro de asistencia</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Metodología</label>
                        <textarea id="crearMetodologia" class="form-control" rows="2" placeholder="Describe la metodología utilizada"></textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">¿La formación es medible en su impacto? <span class="text-danger">*</span></label>
                        <select id="crearImpactoMedible" class="form-select" required>
                            <option value="" selected disabled>Selecciona una opción</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-12 d-none" id="crearIndicadorFields">
                        <div class="border rounded p-3 bg-light-subtle">
                            <h6 class="fw-semibold mb-3">Información del indicador</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nombre del indicador <span class="text-danger">*</span></label>
                                    <input type="text" id="crearIndicadorNombre" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Fórmula del indicador <span class="text-danger">*</span></label>
                                    <textarea id="crearFormulaIndicador" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Frecuencia de medición <span class="text-danger">*</span></label>
                                    <input type="text" id="crearFrecuenciaMedicion" class="form-control" placeholder="Ej: Mensual, trimestral">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Temas</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" id="crearTemaInput" class="form-control" placeholder="Escribe un tema y presiona Agregar o Enter">
                            <button type="button" class="btn btn-outline-primary px-3 flex-shrink-0" onclick="agregarTema()">
                                <i class="ti ti-plus"></i> Agregar
                            </button>
                        </div>
                        <div id="crearTemasLista" class="d-flex flex-wrap gap-2 mb-1"></div>
                        <small class="text-muted">Estos temas serán visibles para los asistentes al abrir el link.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Objetivo de la formación <span class="text-danger">*</span></label>
                        <textarea id="crearObservaciones" class="form-control" rows="3" placeholder="Información adicional sobre la capacitación..."></textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Registrado por</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly style="background:#f8f9fa;">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Instructor <span class="text-danger">*</span></label>
                        <input type="text" id="crearInstructor" class="form-control" value="{{ auth()->user()->name }}" placeholder="Nombre del instructor (puede ser externo)">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Fechas / Sesiones <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="datetime-local" id="crearFechaInput" class="form-control">
                            <button type="button" class="btn btn-outline-primary px-3 flex-shrink-0" onclick="agregarFecha()">
                                <i class="ti ti-plus"></i> Agregar
                            </button>
                        </div>
                        <div id="crearFechasLista" class="d-flex flex-column gap-1 mb-1"></div>
                        <small class="text-muted">Agrega todas las fechas en que se realizará la capacitación.</small>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Duración</label>
                        <select id="crearDuracion" class="form-select">
                            <option value="">— Libre / No aplica —</option>
                            <option value="1">1 hora</option>
                            <option value="2">2 horas</option>
                            <option value="3">3 horas</option>
                            <option value="4">4 horas</option>
                            <option value="5">5 horas</option>
                            <option value="6">6 horas</option>
                            <option value="7">7 horas</option>
                            <option value="8">8 horas</option>
                            <option value="9">9 horas</option>
                            <option value="10">10 horas</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Tiempo límite del link de registro <span class="text-danger">*</span></label>
                        <select id="crearExpiraEn" class="form-select">
                            <option value="0">Sin expiración (control manual)</option>
                            <option value="30">30 minutos</option>
                            <option value="60" selected>1 hora</option>
                            <option value="120">2 horas</option>
                            <option value="240">4 horas</option>
                            <option value="480">8 horas</option>
                            <option value="1440">24 horas</option>
                            <option value="2880">48 horas</option>
                            <option value="10080">7 días</option>
                            <option value="43200">30 días</option>
                        </select>
                        <small class="text-muted">Sin expiración: el link funciona hasta que lo cierres o desactives manualmente.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnCrearCapacitacion" onclick="guardarCapacitacion()">
                    <i class="ti ti-link me-1"></i> Crear y Generar Link
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL EDITAR CAPACITACIÓN
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">
                    <i class="ti ti-edit me-2 text-primary"></i>Editar Capacitación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editarId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                        <input type="text" id="editarTitulo" class="form-control" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Tipo de evento <span class="text-danger">*</span></label>
                        <select id="editarTipoEvento" class="form-select" required>
                            <option value="capacitacion">Capacitación</option>
                            <option value="asistencia">Registro de asistencia</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Metodología</label>
                        <textarea id="editarMetodologia" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">¿La formación es medible en su impacto? <span class="text-danger">*</span></label>
                        <select id="editarImpactoMedible" class="form-select" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-12 d-none" id="editarIndicadorFields">
                        <div class="border rounded p-3 bg-light-subtle">
                            <h6 class="fw-semibold mb-3">Información del indicador</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nombre del indicador <span class="text-danger">*</span></label>
                                    <input type="text" id="editarIndicadorNombre" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Fórmula del indicador <span class="text-danger">*</span></label>
                                    <textarea id="editarFormulaIndicador" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Frecuencia de medición <span class="text-danger">*</span></label>
                                    <input type="text" id="editarFrecuenciaMedicion" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Temas</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" id="editarTemaInput" class="form-control" placeholder="Escribe un tema y presiona Agregar o Enter">
                            <button type="button" class="btn btn-outline-primary px-3 flex-shrink-0" onclick="agregarTemaEditar()">
                                <i class="ti ti-plus"></i> Agregar
                            </button>
                        </div>
                        <div id="editarTemasLista" class="d-flex flex-wrap gap-2 mb-1"></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <textarea id="editarObservaciones" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Instructor</label>
                        <input type="text" id="editarInstructor" class="form-control" placeholder="Nombre del instructor (puede ser externo)">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Fecha de la Capacitación</label>
                        <input type="datetime-local" id="editarFecha" class="form-control">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Duración</label>
                        <select id="editarDuracion" class="form-select">
                            <option value="">— Libre / No aplica —</option>
                            <option value="1">1 hora</option>
                            <option value="2">2 horas</option>
                            <option value="3">3 horas</option>
                            <option value="4">4 horas</option>
                            <option value="5">5 horas</option>
                            <option value="6">6 horas</option>
                            <option value="7">7 horas</option>
                            <option value="8">8 horas</option>
                            <option value="9">9 horas</option>
                            <option value="10">10 horas</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEdicion" onclick="guardarEdicion()">
                    <i class="ti ti-device-floppy me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODAL DETALLE CAPACITACIÓN
═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleLabel">
                    <i class="ti ti-info-circle me-2 text-primary"></i><span id="detalleTitulo">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
div.dataTables_wrapper div.dataTables_length,
div.dataTables_wrapper div.dataTables_filter { padding: 12px 16px 0; }
div.dataTables_wrapper div.dataTables_info,
div.dataTables_wrapper div.dataTables_paginate { padding: 10px 16px 12px; border-top: 1px solid #e9ecef; }
div.dataTables_wrapper div.dataTables_length label,
div.dataTables_wrapper div.dataTables_filter label { font-size: 13px; color: #6c757d; margin-bottom: 8px; }
div.dataTables_wrapper div.dataTables_info { font-size: 13px; color: #6c757d; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
// ── Estado local ──────────────────────────────────────────────
var csrfToken = '{{ csrf_token() }}';
var temasCrear = [];
var fechasCrear = [];
var dtCapacitaciones = null;

var estadoMap = {
    'Abierta':     '<span class="badge bg-success">Abierta</span>',
    'Cerrada':     '<span class="badge bg-secondary">Cerrada</span>',
    'Expirada':    '<span class="badge bg-warning text-dark">Expirada</span>',
    'Desactivada': '<span class="badge bg-danger">Desactivada</span>',
};

$(function () {
    dtCapacitaciones = $('#tablaCapacitaciones').DataTable({
        ajax: {
            url: '/admin/capacitaciones/list',
            dataSrc: '',
            headers: { 'Accept': 'application/json' },
        },
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
        },
        pageLength: 25,
         order: [[4, 'desc']],
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: 'titulo',
                render: function (data) { return escHtml(data || ''); }
            },
            {
                data: 'tipo_evento',
                render: function (data) {
                    return data === 'asistencia'
                        ? '<span class="badge bg-info-subtle text-info-emphasis">Registro de asistencia</span>'
                        : '<span class="badge bg-primary-subtle text-primary">Capacitación</span>';
                }
            },
            {
                data: 'instructor_nombre',
                render: function (data) {
                    return data ? escHtml(data) : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'fecha_capacitacion',
                render: function (data) {
                    return data || '<span class="text-muted">—</span>';
                }
            },
            { data: 'fecha_expiracion' },
            {
                data: 'estado_label',
                render: function (data) {
                    return estadoMap[data] || '<span class="badge bg-secondary">' + escHtml(data) + '</span>';
                }
            },
            {
                data: 'asistentes_count',
                render: function (data) {
                    return '<span class="badge bg-primary rounded-pill">' + (data || 0) + '</span>';
                }
            },
            {
                data: 'encrypted_id',
                orderable: false,
                searchable: false,
                render: function (data) {
                    return '<a href="/admin/capacitaciones/' + encodeURIComponent(data) + '" class="btn btn-sm btn-outline-primary">'
                        + '<i class="ti ti-eye me-1"></i>Ver</a>';
                }
            },
        ],
    });
});

// ── Fechas / Sesiones ─────────────────────────────────────────
function renderFechas() {
    var lista = document.getElementById('crearFechasLista');
    lista.innerHTML = '';
    fechasCrear.forEach(function (f, i) {
        var d = new Date(f);
        var label = d.toLocaleDateString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric' })
                  + ' ' + d.toLocaleTimeString('es-CO', { hour:'2-digit', minute:'2-digit' });
        var div = document.createElement('div');
        div.className = 'd-flex align-items-center justify-content-between px-3 py-2 rounded border';
        div.style.fontSize = '13px';
        div.innerHTML = '<span><i class="ti ti-calendar me-1 text-primary"></i>' + label + '</span>'
            + '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarFecha(' + i + ')">'
            + '<i class="ti ti-x"></i></button>';
        lista.appendChild(div);
    });
}

function agregarFecha() {
    var input = document.getElementById('crearFechaInput');
    var val = input.value;
    if (!val) return;
    if (fechasCrear.indexOf(val) === -1) { fechasCrear.push(val); renderFechas(); }
    input.value = '';
}

function eliminarFecha(i) {
    fechasCrear.splice(i, 1);
    renderFechas();
}

function toggleIndicadorFields(prefix) {
    var visible = document.getElementById(prefix + 'ImpactoMedible').value === '1';
    document.getElementById(prefix + 'IndicadorFields').classList.toggle('d-none', !visible);
}

document.getElementById('crearFechaInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); agregarFecha(); }
});
document.getElementById('crearImpactoMedible').addEventListener('change', function () {
    toggleIndicadorFields('crear');
});
document.getElementById('editarImpactoMedible').addEventListener('change', function () {
    toggleIndicadorFields('editar');
});

// ── Temas (modal crear) ───────────────────────────────────────
function renderTemas() {
    var lista = document.getElementById('crearTemasLista');
    lista.innerHTML = '';
    temasCrear.forEach(function (t, i) {
        var span = document.createElement('span');
        span.className = 'badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 px-2 py-1';
        span.style.fontSize = '13px';
        span.innerHTML = t + ' <button type="button" class="btn-close btn-close-sm" style="font-size:10px;" onclick="eliminarTema(' + i + ')"></button>';
        lista.appendChild(span);
    });
}

function agregarTema() {
    var input = document.getElementById('crearTemaInput');
    var val = input.value.trim();
    if (!val) return;
    if (temasCrear.indexOf(val) === -1) {
        temasCrear.push(val);
        renderTemas();
    }
    input.value = '';
    input.focus();
}

function eliminarTema(i) {
    temasCrear.splice(i, 1);
    renderTemas();
}

document.getElementById('crearTemaInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); agregarTema(); }
});

// ── Abrir modal crear ─────────────────────────────────────────
function abrirModalCrear() {
    temasCrear  = [];
    fechasCrear = [];
    renderTemas();
    renderFechas();
    document.getElementById('crearTitulo').value       = '';
    document.getElementById('crearTipoEvento').value   = '';
    document.getElementById('crearObservaciones').value = '';
    document.getElementById('crearMetodologia').value   = '';
    document.getElementById('crearImpactoMedible').value = '';
    document.getElementById('crearIndicadorNombre').value = '';
    document.getElementById('crearFormulaIndicador').value = '';
    document.getElementById('crearFrecuenciaMedicion').value = '';
    toggleIndicadorFields('crear');
    document.getElementById('crearInstructor').value   = '{{ auth()->user()->name }}';
    document.getElementById('crearFechaInput').value   = '';
    document.getElementById('crearExpiraEn').value     = '60';
    document.getElementById('crearDuracion').value     = '';
    document.getElementById('crearTemaInput').value    = '';
    var modal = new bootstrap.Modal(document.getElementById('modalCrear'));
    modal.show();
}

// ── Guardar capacitación ──────────────────────────────────────
async function guardarCapacitacion() {
    var titulo        = document.getElementById('crearTitulo').value.trim();
    var tipoEvento    = document.getElementById('crearTipoEvento').value;
    var impactoMedible = document.getElementById('crearImpactoMedible').value;
    var instructor    = document.getElementById('crearInstructor').value.trim();
    var observaciones = document.getElementById('crearObservaciones').value.trim();

    if (!titulo) {
        Swal.fire({ icon: 'warning', title: 'Título requerido', text: 'Ingresa un título para la capacitación.', confirmButtonColor: '#1ab394' });
        return;
    }
    if (!tipoEvento) {
        Swal.fire({ icon: 'warning', title: 'Tipo de evento requerido', text: 'Selecciona si es una capacitación o un registro de asistencia.', confirmButtonColor: '#1ab394' });
        return;
    }
    if (impactoMedible === '') {
        Swal.fire({ icon: 'warning', title: 'Respuesta requerida', text: 'Indica si la formación es medible en su impacto.', confirmButtonColor: '#1ab394' });
        return;
    }
    if (impactoMedible === '1' && (
        !document.getElementById('crearIndicadorNombre').value.trim() ||
        !document.getElementById('crearFormulaIndicador').value.trim() ||
        !document.getElementById('crearFrecuenciaMedicion').value.trim()
    )) {
        Swal.fire({ icon: 'warning', title: 'Datos del indicador requeridos', text: 'Completa el nombre, fórmula y frecuencia de medición.', confirmButtonColor: '#1ab394' });
        return;
    }
    if (!observaciones) {
        Swal.fire({ icon: 'warning', title: 'Observaciones requeridas', text: 'Ingresa las observaciones de la capacitación.', confirmButtonColor: '#1ab394' });
        return;
    }
    if (!instructor) {
        Swal.fire({ icon: 'warning', title: 'Instructor requerido', text: 'Ingresa el nombre del instructor.', confirmButtonColor: '#1ab394' });
        return;
    }
    if (fechasCrear.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Fecha requerida', text: 'Agrega al menos una fecha de sesión.', confirmButtonColor: '#1ab394' });
        return;
    }

    var btn = document.getElementById('btnCrearCapacitacion');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creando...';

    try {
        var res = await fetch('/admin/capacitaciones', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                titulo:             titulo,
                tipo_evento:        tipoEvento,
                temas:              JSON.stringify(temasCrear),
                metodologia:        document.getElementById('crearMetodologia').value.trim() || null,
                impacto_medible:    impactoMedible,
                indicador_nombre:   document.getElementById('crearIndicadorNombre').value.trim() || null,
                formula_indicador:  document.getElementById('crearFormulaIndicador').value.trim() || null,
                frecuencia_medicion: document.getElementById('crearFrecuenciaMedicion').value.trim() || null,
                fechas_sesiones:    JSON.stringify(fechasCrear),
                observaciones:      document.getElementById('crearObservaciones').value.trim() || null,
                instructor_nombre:  document.getElementById('crearInstructor').value.trim() || null,
                fecha_capacitacion: fechasCrear[0] || null,
                duracion_horas:     document.getElementById('crearDuracion').value || null,
                expira_en:          parseInt(document.getElementById('crearExpiraEn').value),
            }),
        });

        var json = await res.json();

        if (!res.ok || !json.success) {
            throw new Error(json.message || 'Error al crear la capacitación.');
        }

        if (json.total > 1) {
            window.location.reload();
        } else {
            window.location.href = '/admin/capacitaciones/' + encodeURIComponent(json.capacitacion.encrypted_id);
        }

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#1ab394' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-link me-1"></i> Crear y Generar Link';
    }
}

function copiarSwalLink() {
    var inp = document.getElementById('swalLink');
    inp.select();
    document.execCommand('copy');
    inp.blur();
}

// ── Ver detalle ───────────────────────────────────────────────
async function verDetalle(id) {
    document.getElementById('detalleTitulo').textContent = '—';
    document.getElementById('detalleContenido').innerHTML =
        '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';

    var modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    modal.show();

    try {
        var res  = await fetch('/admin/capacitaciones/' + id + '/show', { headers: { 'Accept': 'application/json' } });
        var data = await res.json();

        document.getElementById('detalleTitulo').textContent = data.titulo;
        document.getElementById('detalleContenido').innerHTML = renderDetalle(data);

    } catch (err) {
        document.getElementById('detalleContenido').innerHTML =
            '<div class="alert alert-danger">Error al cargar el detalle.</div>';
    }
}

function renderDetalle(d) {
    var vigenteBadge = d.vigente
        ? '<span class="badge bg-success">Vigente</span>'
        : '<span class="badge bg-secondary">Expirado</span>';

    var temasHtml = (d.temas && d.temas.length)
        ? d.temas.map(function (t) {
            return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1 mb-1">' + escHtml(t) + '</span>';
          }).join('')
        : '<span class="text-muted small">Sin temas registrados</span>';

    var obsHtml = d.observaciones
        ? '<hr><h6 class="fw-semibold mb-1"><i class="ti ti-notes text-primary me-1"></i>Observaciones</h6>'
          + '<p class="mb-0 small" style="white-space:pre-line;">' + escHtml(d.observaciones) + '</p>'
        : '';

    var metodologiaHtml = d.metodologia
        ? '<hr><h6 class="fw-semibold mb-1">Metodología</h6>'
          + '<p class="mb-0 small" style="white-space:pre-line;">' + escHtml(d.metodologia) + '</p>'
        : '';

    var indicadorHtml = d.impacto_medible
        ? '<hr><h6 class="fw-semibold mb-2">Medición de impacto</h6>'
          + '<dl class="row mb-0 small">'
          + '<dt class="col-5">Indicador</dt><dd class="col-7">' + escHtml(d.indicador_nombre || '—') + '</dd>'
          + '<dt class="col-5">Fórmula</dt><dd class="col-7">' + escHtml(d.formula_indicador || '—') + '</dd>'
          + '<dt class="col-5">Frecuencia</dt><dd class="col-7">' + escHtml(d.frecuencia_medicion || '—') + '</dd>'
          + '</dl>'
        : '<hr><p class="small text-muted mb-0">La formación no es medible en su impacto.</p>';

    var editarHtml = '';
    @can('capacitaciones.editar')
    editarHtml = '<hr>'
        + '<button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="abrirEditar(' + JSON.stringify(d).replace(/"/g, '&quot;') + ')">'
        + '<i class="ti ti-edit me-1"></i>Editar Capacitación</button>';
    @endcan

    var desactivarHtml = '';
    @can('capacitaciones.eliminar')
    if (d.activo) {
        desactivarHtml = '<button class="btn btn-outline-danger btn-sm w-100" onclick="desactivar(' + d.id + ')">'
            + '<i class="ti ti-x me-1"></i>Desactivar Capacitación</button>';
    }
    @endcan

    // Asistentes
    var asistentesHtml = '';
    if (d.asistentes && d.asistentes.length > 0) {
        var filas = d.asistentes.map(function (a, i) {
            return '<tr>'
                + '<td>' + (i + 1) + '</td>'
                + '<td>' + escHtml(a.nombre) + '</td>'
                + '<td>' + escHtml(a.correo) + '</td>'
                + '<td>' + (a.telefono ? escHtml(a.telefono) : '—') + '</td>'
                + '<td>' + (a.created_at || '—') + '</td>'
                + '</tr>';
        }).join('');

        asistentesHtml = '<div class="table-responsive">'
            + '<table class="table table-sm table-hover align-middle" id="tablaAsistentesModal">'
            + '<thead><tr><th>#</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Fecha Registro</th></tr></thead>'
            + '<tbody>' + filas + '</tbody>'
            + '</table></div>';
    } else {
        asistentesHtml = '<div class="text-center text-muted py-4">'
            + '<i class="ti ti-users" style="font-size:40px;opacity:.3;"></i>'
            + '<p class="mt-2 mb-0">Aún no hay asistentes registrados.</p>'
            + '<p class="small">Comparte el link para que los participantes se registren.</p>'
            + '</div>';
    }

    var exportBtn = (d.asistentes && d.asistentes.length > 0)
        ? '<button class="btn btn-sm btn-outline-success" onclick="exportarCSV(' + d.id + ')">'
          + '<i class="ti ti-file-export me-1"></i>Exportar CSV</button>'
        : '';

    return '<div class="row g-3">'
        // ── Panel izquierdo ──
        + '<div class="col-12 col-lg-4">'
        + '<div class="card border-0 bg-light h-100">'
        + '<div class="card-body">'
        + '<h6 class="fw-semibold mb-3"><i class="ti ti-info-circle text-primary me-1"></i>Información</h6>'
        + '<dl class="row mb-0 small">'
        + '<dt class="col-5">Tipo</dt><dd class="col-7">' + (d.tipo_evento === 'asistencia' ? 'Registro de asistencia' : 'Capacitación') + '</dd>'
        + '<dt class="col-5">Registrado por</dt><dd class="col-7">' + escHtml(d.creado_por_nombre) + '</dd>'
        + '<dt class="col-5">Instructor</dt><dd class="col-7">' + (d.instructor_nombre ? escHtml(d.instructor_nombre) : '—') + '</dd>'
        + '<dt class="col-5">Duración</dt><dd class="col-7">' + (d.duracion_horas ? d.duracion_horas + (isNaN(d.duracion_horas) ? '' : ' hora(s)') : '—') + '</dd>'
        + '<dt class="col-5">Fecha</dt><dd class="col-7">' + (d.fecha_capacitacion || '—') + '</dd>'
        + '<dt class="col-5">Link válido</dt><dd class="col-7">' + formatearExpiracion(d.expira_en) + '</dd>'
        + '<dt class="col-5">Expira</dt><dd class="col-7">' + d.fecha_expiracion + '</dd>'
        + '<dt class="col-5">Estado</dt><dd class="col-7">' + vigenteBadge + '</dd>'
        + '</dl>'
        + '<hr>'
        + '<h6 class="fw-semibold mb-2"><i class="ti ti-list text-primary me-1"></i>Temas</h6>'
         + '<div>' + temasHtml + '</div>'
         + metodologiaHtml
         + indicadorHtml
         + obsHtml
        + '<hr>'
        + '<h6 class="fw-semibold mb-2"><i class="ti ti-link text-primary me-1"></i>Link de Registro</h6>'
        + '<div class="input-group mb-2">'
        + '<input type="text" id="linkRegistro_' + d.id + '" class="form-control form-control-sm" value="' + d.link + '" readonly>'
        + '<button class="btn btn-outline-secondary btn-sm" onclick="copiarLink(' + d.id + ')" title="Copiar link">'
        + '<i class="ti ti-copy"></i></button></div>'
        + (d.vigente ? '' : '<p class="text-danger small mb-0"><i class="ti ti-clock me-1"></i>Este link ya expiró.</p>')
        + editarHtml
        + desactivarHtml
        + '</div></div></div>'
        // ── Panel derecho ──
        + '<div class="col-12 col-lg-8">'
        + '<div class="card border-0 bg-light h-100">'
        + '<div class="card-body">'
        + '<div class="d-flex justify-content-between align-items-center mb-3">'
        + '<h6 class="fw-semibold mb-0"><i class="ti ti-users text-primary me-1"></i>Asistentes Registrados '
        + '<span class="badge bg-primary ms-1">' + (d.asistentes ? d.asistentes.length : 0) + '</span></h6>'
        + exportBtn
        + '</div>'
        + asistentesHtml
        + '</div></div></div>'
        + '</div>';
}

function formatearExpiracion(min) {
    if (min < 60)   return min + ' min';
    if (min < 1440) return (min / 60) + ' hora(s)';
    return (min / 1440) + ' día(s)';
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Copiar link ───────────────────────────────────────────────
function copiarLink(id) {
    var inp = document.getElementById('linkRegistro_' + id);
    if (!inp) return;
    inp.select();
    document.execCommand('copy');
    inp.blur();
    var btn = inp.nextElementSibling;
    if (btn) {
        btn.innerHTML = '<i class="ti ti-check text-success"></i>';
        setTimeout(function () { btn.innerHTML = '<i class="ti ti-copy"></i>'; }, 2000);
    }
}

// ── Desactivar ────────────────────────────────────────────────
function desactivar(id) {
    Swal.fire({
        icon: 'warning',
        title: '¿Desactivar capacitación?',
        text: 'El link de registro dejará de funcionar.',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
    }).then(async function (result) {
        if (!result.isConfirmed) return;
        try {
            var res = await fetch('/admin/capacitaciones/' + id + '/desactivar', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            if (!res.ok) throw new Error();
            bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();
            if (dtCapacitaciones) dtCapacitaciones.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Desactivada', timer: 1500, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo desactivar.', confirmButtonColor: '#1ab394' });
        }
    });
}

// ── Editar ────────────────────────────────────────────────────
var temasEditar = [];

function renderTemasEditar() {
    var lista = document.getElementById('editarTemasLista');
    lista.innerHTML = '';
    temasEditar.forEach(function (t, i) {
        var span = document.createElement('span');
        span.className = 'badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 px-2 py-1';
        span.style.fontSize = '13px';
        span.innerHTML = t + ' <button type="button" class="btn-close btn-close-sm" style="font-size:10px;" onclick="eliminarTemaEditar(' + i + ')"></button>';
        lista.appendChild(span);
    });
}

function agregarTemaEditar() {
    var input = document.getElementById('editarTemaInput');
    var val = input.value.trim();
    if (!val) return;
    if (temasEditar.indexOf(val) === -1) { temasEditar.push(val); renderTemasEditar(); }
    input.value = '';
    input.focus();
}

function eliminarTemaEditar(i) {
    temasEditar.splice(i, 1);
    renderTemasEditar();
}

document.getElementById('editarTemaInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); agregarTemaEditar(); }
});

function abrirEditar(d) {
    bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();

    document.getElementById('editarId').value            = d.id;
    document.getElementById('editarTitulo').value        = d.titulo || '';
    document.getElementById('editarTipoEvento').value    = d.tipo_evento || 'capacitacion';
    document.getElementById('editarMetodologia').value    = d.metodologia || '';
    document.getElementById('editarImpactoMedible').value = d.impacto_medible ? '1' : '0';
    document.getElementById('editarIndicadorNombre').value = d.indicador_nombre || '';
    document.getElementById('editarFormulaIndicador').value = d.formula_indicador || '';
    document.getElementById('editarFrecuenciaMedicion').value = d.frecuencia_medicion || '';
    toggleIndicadorFields('editar');
    document.getElementById('editarObservaciones').value = d.observaciones || '';
    document.getElementById('editarInstructor').value    = d.instructor_nombre || '';
    document.getElementById('editarDuracion').value      = d.duracion_horas || '';
    document.getElementById('editarTemaInput').value     = '';

    // Fecha: convertir "dd/mm/YYYY HH:mm" a "YYYY-MM-DDTHH:mm"
    var fechaVal = '';
    if (d.fecha_capacitacion && d.fecha_capacitacion !== '—') {
        var parts = d.fecha_capacitacion.match(/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})/);
        if (parts) fechaVal = parts[3] + '-' + parts[2] + '-' + parts[1] + 'T' + parts[4] + ':' + parts[5];
    }
    document.getElementById('editarFecha').value = fechaVal;

    temasEditar = Array.isArray(d.temas) ? d.temas.slice() : [];
    renderTemasEditar();

    setTimeout(function () {
        new bootstrap.Modal(document.getElementById('modalEditar')).show();
    }, 300);
}

async function guardarEdicion() {
    var id     = document.getElementById('editarId').value;
    var titulo = document.getElementById('editarTitulo').value.trim();
    var impactoMedible = document.getElementById('editarImpactoMedible').value;
    if (!titulo) {
        Swal.fire({ icon: 'warning', title: 'Título requerido', confirmButtonColor: '#1ab394' });
        return;
    }
    if (impactoMedible === '1' && (
        !document.getElementById('editarIndicadorNombre').value.trim() ||
        !document.getElementById('editarFormulaIndicador').value.trim() ||
        !document.getElementById('editarFrecuenciaMedicion').value.trim()
    )) {
        Swal.fire({ icon: 'warning', title: 'Datos del indicador requeridos', text: 'Completa el nombre, fórmula y frecuencia de medición.', confirmButtonColor: '#1ab394' });
        return;
    }

    var btn = document.getElementById('btnGuardarEdicion');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        var res = await fetch('/admin/capacitaciones/' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                titulo:             titulo,
                tipo_evento:        document.getElementById('editarTipoEvento').value,
                temas:              JSON.stringify(temasEditar),
                metodologia:        document.getElementById('editarMetodologia').value.trim() || null,
                impacto_medible:    impactoMedible,
                indicador_nombre:   document.getElementById('editarIndicadorNombre').value.trim() || null,
                formula_indicador:  document.getElementById('editarFormulaIndicador').value.trim() || null,
                frecuencia_medicion: document.getElementById('editarFrecuenciaMedicion').value.trim() || null,
                observaciones:      document.getElementById('editarObservaciones').value.trim() || null,
                instructor_nombre:  document.getElementById('editarInstructor').value.trim() || null,
                fecha_capacitacion: document.getElementById('editarFecha').value || null,
                duracion_horas:     document.getElementById('editarDuracion').value || null,
            }),
        });

        if (!res.ok) throw new Error();

        bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
        if (dtCapacitaciones) dtCapacitaciones.ajax.reload(null, false);
        Swal.fire({ icon: 'success', title: '¡Guardado!', timer: 1500, showConfirmButton: false });

    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar.', confirmButtonColor: '#1ab394' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Guardar Cambios';
    }
}

// ── Exportar CSV ──────────────────────────────────────────────
function exportarCSV(id) {
    var tabla = document.getElementById('tablaAsistentesModal');
    if (!tabla) return;
    var rows = [['#', 'Nombre', 'Correo', 'Teléfono', 'Fecha Registro']];
    tabla.querySelectorAll('tbody tr').forEach(function (tr) {
        var cols = tr.querySelectorAll('td');
        rows.push([cols[0].innerText, cols[1].innerText, cols[2].innerText, cols[3].innerText, cols[4].innerText]);
    });
    var csv  = rows.map(function (r) { return r.map(function (c) { return '"' + c.replace(/"/g, '""') + '"'; }).join(','); }).join('\n');
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var a    = document.createElement('a');
    a.href   = URL.createObjectURL(blob);
    a.download = 'asistentes_capacitacion_' + id + '.csv';
    a.click();
}
</script>
@endpush
@endsection
