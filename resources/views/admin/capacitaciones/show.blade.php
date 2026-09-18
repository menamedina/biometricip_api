@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 mb-4" style="background: linear-gradient(135deg,#1ab394,#0d7560);">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-white">{{ $cap->titulo }}</h4>
                        <p class="mb-0 text-white" style="opacity:.85;font-size:14px;">
                            {{ auth()->user()->empresa?->nombre ?? 'BiometricIP' }}
                            @if($cap->instructor_nombre)
                                &nbsp;&bull;&nbsp; Instructor: {{ $cap->instructor_nombre }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('admin.capacitaciones.index') }}" class="btn btn-light fw-semibold">
                        <i class="ti ti-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Panel izquierdo: info ──────────────────────────── --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h6 class="fw-bold mb-3"><i class="ti ti-info-circle text-primary me-1"></i>Información</h6>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Registrado por</dt>
                        <dd class="col-7">{{ $creadoPorNombre }}</dd>

                        <dt class="col-5 text-muted">Instructor</dt>
                        <dd class="col-7">{{ $cap->instructor_nombre ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Fecha</dt>
                        <dd class="col-7">{{ $cap->fecha_capacitacion?->format('d/m/Y H:i') ?? '—' }}</dd>

                        <dt class="col-5 text-muted">Duración</dt>
                        <dd class="col-7">
                            @if($cap->duracion_horas)
                                {{ $cap->duracion_horas }}{{ is_numeric($cap->duracion_horas) ? ' hora(s)' : '' }}
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-5 text-muted">Link válido</dt>
                        <dd class="col-7">
                            {{ $cap->expira_en >= 1440 ? ($cap->expira_en / 1440).' día(s)' : ($cap->expira_en >= 60 ? ($cap->expira_en / 60).' hora(s)' : $cap->expira_en.' min') }}
                        </dd>

                        <dt class="col-5 text-muted">Expira</dt>
                        <dd class="col-7">{{ $cap->fecha_expiracion->format('d/m/Y H:i') }}</dd>

                        <dt class="col-5 text-muted">Estado</dt>
                        <dd class="col-7">
                            @if(!$cap->activo)
                                <span class="badge bg-danger">Desactivada</span>
                            @elseif($cap->cerrada)
                                <span class="badge bg-secondary">Cerrada</span>
                            @elseif(!$cap->estaVigente())
                                <span class="badge bg-warning text-dark">Expirada</span>
                            @else
                                <span class="badge bg-success">Abierta</span>
                            @endif
                        </dd>
                    </dl>

                    @if($cap->temas && count($cap->temas))
                    <hr>
                    <h6 class="fw-bold mb-2"><i class="ti ti-list text-primary me-1"></i>Temas</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($cap->temas as $tema)
                            <span class="badge rounded-pill"
                                style="background:#e8f7f4;color:#0d7560;border:1px solid #b2dfdb;font-size:12px;font-weight:500;">
                                {{ $tema }}
                            </span>
                        @endforeach
                    </div>
                    @endif

                    @if($cap->observaciones)
                    <hr>
                    <h6 class="fw-bold mb-2"><i class="ti ti-notes text-primary me-1"></i>Observaciones</h6>
                    <p class="mb-0 small" style="white-space:pre-line;">{{ $cap->observaciones }}</p>
                    @endif

                    <hr>
                    <h6 class="fw-bold mb-2"><i class="ti ti-link text-primary me-1"></i>Link de Registro</h6>
                    <div class="p-3 mb-2 rounded small" id="linkTexto"
                        style="background:#f0faf7;border:2px dashed #1ab394;font-family:monospace;word-break:break-all;color:#0d7560;">
                        {{ $link }}
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <button class="btn btn-primary flex-grow-1" onclick="copiarLink()">
                            <i class="ti ti-copy me-1"></i> Copiar Link
                        </button>
                        <button class="btn btn-outline-primary px-3" onclick="verQR()" title="Ver código QR">
                            <i class="ti ti-qrcode"></i>
                        </button>
                        @can('capacitaciones.editar')
                        <button class="btn btn-outline-secondary px-3" onclick="regenerarLink()" title="Regenerar link">
                            <i class="ti ti-refresh"></i>
                        </button>
                        @endcan
                    </div>
                    @if(!$cap->aceptaRegistros())
                        <p class="text-danger small mb-1"><i class="ti ti-clock me-1"></i>Este link ya expiró. Los asistentes no pueden registrarse.</p>
                        @can('capacitaciones.editar')
                        <div class="d-flex gap-2 align-items-center">
                            <select id="selectHabilitarLink" class="form-select form-select-sm">
                                <option value="0">Sin expiración</option>
                                <option value="60">+1 hora</option>
                                <option value="120">+2 horas</option>
                                <option value="240">+4 horas</option>
                                <option value="480">+8 horas</option>
                                <option value="1440">+24 horas</option>
                                <option value="10080">+7 días</option>
                            </select>
                            <button class="btn btn-success btn-sm flex-shrink-0" onclick="habilitarLink()">
                                <i class="ti ti-plug me-1"></i> Habilitar
                            </button>
                        </div>
                        @endcan
                    @endif

                    @can('capacitaciones.editar')
                    <hr>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="abrirEditar()">
                        <i class="ti ti-edit me-1"></i> Editar Capacitación
                    </button>

                    @if($cap->activo)
                        @if($cap->cerrada)
                        <button type="button" class="btn btn-outline-success btn-sm w-100 mb-2" onclick="cambiarEstado('abrir')">
                            <i class="ti ti-lock-open me-1"></i> Abrir Capacitación
                        </button>
                        @else
                        <button type="button" class="btn btn-outline-warning btn-sm w-100 mb-2" onclick="cambiarEstado('cerrar')">
                            <i class="ti ti-lock me-1"></i> Cerrar Capacitación
                        </button>
                        @endif
                    @endif
                    @endcan

                    @can('capacitaciones.eliminar')
                    @if($cap->activo)
                    <form method="POST" action="{{ route('admin.capacitaciones.desactivar', Crypt::encryptString((string)$cap->id)) }}"
                        onsubmit="return confirm('¿Desactivar permanentemente? El link dejará de funcionar para siempre.')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="ti ti-x me-1"></i> Desactivar Capacitación
                        </button>
                    </form>
                    @endif
                    @endcan

                </div>
            </div>
        </div>

        {{-- ── Panel derecho: asistentes ──────────────────────── --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <i class="ti ti-users text-primary me-1"></i>
                            Participantes
                            <span class="badge bg-primary ms-1">{{ $cap->asistentes->count() }}</span>
                            <span class="badge bg-success ms-1" title="Confirmados">{{ $cap->asistentes->where('estado','confirmado')->count() }} ✓</span>
                        </h6>
                        <div class="d-flex gap-2">
                            @can('capacitaciones.editar')
                            <button onclick="abrirModalAgregar()" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-user-plus me-1"></i> Agregar
                            </button>
                            @endcan
                            @if($cap->asistentes->count() > 0)
                            <button onclick="exportarCSV()" class="btn btn-sm btn-outline-success">
                                <i class="ti ti-file-spreadsheet me-1"></i> Excel
                            </button>
                            @endif
                        </div>
                    </div>

                    @if($cap->asistentes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small" id="tablaAsistentes">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cédula</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Estado</th>
                                    <th>Confirmación</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cap->asistentes as $i => $asistente)
                                <tr id="fila-asistente-{{ $asistente->id }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $asistente->cedula ?? '—' }}</td>
                                    <td>{{ $asistente->nombre }}</td>
                                    <td>{{ $asistente->correo ?? '—' }}</td>
                                    <td>
                                        @if($asistente->estado === 'confirmado')
                                            <span class="badge bg-success">Confirmado</span>
                                        @else
                                            <span class="badge bg-secondary">Programado</span>
                                        @endif
                                    </td>
                                    <td>{{ $asistente->fecha_confirmacion?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        @if($asistente->estado === 'programado')
                                        @can('capacitaciones.editar')
                                        <button class="btn btn-sm btn-link text-danger p-0"
                                            onclick="eliminarParticipante({{ $asistente->id }})"
                                            title="Quitar participante">
                                            <i class="ti ti-x"></i>
                                        </button>
                                        @endcan
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="text-center text-muted py-5" id="emptyAsistentes">
                            <i class="ti ti-users" style="font-size:48px;opacity:.3;"></i>
                            <p class="mt-2 mb-0">Aún no hay participantes.</p>
                            <p class="small">Agrega participantes o comparte el link.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="ti ti-edit me-2 text-primary"></i>Editar Capacitación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                        <input type="text" id="editTitulo" class="form-control" value="{{ $cap->titulo }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Temas</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" id="editTemaInput" class="form-control" placeholder="Escribe un tema y presiona Agregar o Enter">
                            <button type="button" class="btn btn-outline-primary px-3 flex-shrink-0" onclick="agregarTemaEdit()">
                                <i class="ti ti-plus"></i> Agregar
                            </button>
                        </div>
                        <div id="editTemasLista" class="d-flex flex-wrap gap-2 mb-1"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones <span class="text-danger">*</span></label>
                        <textarea id="editObservaciones" class="form-control" rows="3">{{ $cap->observaciones }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Instructor <span class="text-danger">*</span></label>
                        <input type="text" id="editInstructor" class="form-control" value="{{ $cap->instructor_nombre }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Fecha de la Capacitación <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="editFecha" class="form-control"
                            value="{{ $cap->fecha_capacitacion?->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Duración</label>
                        <select id="editDuracion" class="form-select">
                            <option value="">— Libre / No aplica —</option>
                            @for($h = 1; $h <= 10; $h++)
                                <option value="{{ $h }}" {{ $cap->duracion_horas == $h ? 'selected' : '' }}>{{ $h }} hora{{ $h > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="editCerrada" class="form-select">
                            <option value="0" {{ !$cap->cerrada ? 'selected' : '' }}>Abierta</option>
                            <option value="1" {{ $cap->cerrada ? 'selected' : '' }}>Cerrada</option>
                        </select>
                        <small class="text-muted">Cerrada: los asistentes no pueden registrarse.</small>
                    </div>

                    <div class="col-12">
                        <hr class="my-1">
                        <label class="form-label fw-semibold">
                            <i class="ti ti-clock me-1 text-primary"></i>Extender / Reactivar link de registro
                        </label>
                        <select id="editExtenderLink" class="form-select">
                            <option value="">— No cambiar —</option>
                            <option value="30">+30 minutos</option>
                            <option value="60">+1 hora</option>
                            <option value="120">+2 horas</option>
                            <option value="240">+4 horas</option>
                            <option value="480">+8 horas</option>
                            <option value="1440">+24 horas</option>
                            <option value="2880">+48 horas</option>
                            <option value="10080">+7 días</option>
                        </select>
                        <small class="text-muted">
                            Expira actualmente: <strong>{{ $cap->fecha_expiracion->format('d/m/Y H:i') }}</strong>.
                            Selecciona una opción para extender desde ahora y reactivar el link.
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEdit" onclick="guardarEdicion()">
                    <i class="ti ti-device-floppy me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Agregar Participante --}}
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="ti ti-user-plus me-2 text-primary"></i>Agregar Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" id="tabsAgregar" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabBuscar" type="button">
                            <i class="ti ti-search me-1"></i> Buscar Empleado
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabArea" type="button" onclick="cargarDepartamentos()">
                            <i class="ti ti-building me-1"></i> Por Área
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabManual" type="button">
                            <i class="ti ti-forms me-1"></i> Manual
                        </button>
                    </li>
                </ul>

                <div id="agregarError" class="alert alert-danger d-none"></div>
                <div id="agregarSuccess" class="alert alert-success d-none"></div>

                <div class="tab-content">

                    {{-- Tab: Buscar Empleado --}}
                    <div class="tab-pane fade show active" id="tabBuscar">
                        <div class="mb-3">
                            <input type="text" id="buscarQ" class="form-control"
                                placeholder="Buscar por nombre o cédula..." oninput="buscarEmpleados()">
                        </div>
                        <div id="listaBuscar" style="max-height:300px;overflow-y:auto;">
                            <p class="text-muted small text-center mt-3">Escribe para buscar empleados.</p>
                        </div>
                        <div class="mt-2 d-flex justify-content-end">
                            <button class="btn btn-primary" id="btnAgregarBuscar" onclick="agregarSeleccionadosBuscar(this)" style="display:none!important;">
                                <i class="ti ti-user-plus me-1"></i> Agregar seleccionados
                            </button>
                        </div>
                    </div>

                    {{-- Tab: Por Área --}}
                    <div class="tab-pane fade" id="tabArea">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Área / Departamento</label>
                            <select id="selectDepartamento" class="form-select" onchange="cargarEmpleadosPorArea()">
                                <option value="">— Selecciona un área —</option>
                                <option value="all">Todos los departamentos</option>
                            </select>
                        </div>
                        <div id="listaArea" style="max-height:280px;overflow-y:auto;"></div>
                        <div class="mt-2 d-flex justify-content-between align-items-center" id="panelAreaAcciones" style="display:none!important;">
                            <small class="text-muted"><span id="cntArea">0</span> seleccionado(s)</small>
                            <button class="btn btn-primary btn-sm" id="btnAgregarArea" onclick="agregarSeleccionadosArea(this)">
                                <i class="ti ti-user-plus me-1"></i> Agregar seleccionados
                            </button>
                        </div>
                    </div>

                    {{-- Tab: Manual --}}
                    <div class="tab-pane fade" id="tabManual">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
                                <input type="text" id="agregarCedula" class="form-control" placeholder="Ej: 1234567890">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" id="agregarNombre" class="form-control" placeholder="Nombre del participante">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Correo <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="email" id="agregarCorreo" class="form-control" placeholder="correo@ejemplo.com">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Teléfono <span class="text-muted fw-normal">(opcional)</span></label>
                                <input type="tel" id="agregarTelefono" class="form-control" placeholder="300 123 4567">
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" id="btnGuardarParticipante" onclick="guardarParticipante()">
                                <i class="ti ti-user-plus me-1"></i> Agregar
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal QR --}}
<div class="modal fade" id="modalQR" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="ti ti-qrcode me-2 text-primary"></i>Código QR de Registro</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="qrImage" src="" alt="QR" class="img-fluid rounded" style="max-width:280px;">
                <p class="mt-3 mb-0 small text-muted">Escanea con la cámara para acceder al formulario de registro.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a id="qrDownload" href="#" download="qr_capacitacion_{{ $cap->id }}.png" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-download me-1"></i> Descargar QR
                </a>
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script>
var dtAsistentes = null;

$(function () {
    if ($('#tablaAsistentes').length) {
        dtAsistentes = $('#tablaAsistentes').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[2, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 6] }
            ],
            drawCallback: function () {
                // Renumerar la columna #
                this.api().rows({ page: 'current' }).every(function (rowIdx) {
                    var node = this.node();
                    var pageInfo = dtAsistentes.page.info();
                    $(node).find('td:first').text(pageInfo.start + rowIdx + 1);
                });
            }
        });
    }
});

function copiarLink() {
    var texto = document.getElementById('linkTexto').innerText.trim();
    var ta = document.createElement('textarea');
    ta.value = texto;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    var btn = document.querySelector('[onclick="copiarLink()"]');
    var orig = btn.innerHTML;
    btn.innerHTML = '<i class="ti ti-check me-1"></i> ¡Copiado!';
    btn.classList.replace('btn-primary', 'btn-success');
    setTimeout(function () {
        btn.innerHTML = orig;
        btn.classList.replace('btn-success', 'btn-primary');
    }, 2000);
}

function verQR() {
    var link = document.getElementById('linkTexto').innerText.trim();
    var encoded = encodeURIComponent(link);
    var src = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encoded;
    document.getElementById('qrImage').src = src;
    document.getElementById('qrDownload').href = src + '&format=png';
    new bootstrap.Modal(document.getElementById('modalQR')).show();
}

// ── Regenerar link ────────────────────────────────────────────
async function regenerarLink() {
    if (!confirm('¿Regenerar el link? El link anterior dejará de funcionar.')) return;
    try {
        var res = await fetch('/admin/capacitaciones/{{ urlencode(Crypt::encryptString((string)$cap->id)) }}/regenerar', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error();
        window.location.reload();
    } catch (e) {
        alert('Error al regenerar el link.');
    }
}

// ── Habilitar link ────────────────────────────────────────────
async function habilitarLink() {
    var minutos = document.getElementById('selectHabilitarLink').value;
    try {
        var res = await fetch('/admin/capacitaciones/{{ urlencode(Crypt::encryptString((string)$cap->id)) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                titulo:             '{{ addslashes($cap->titulo) }}',
                observaciones:      '{{ addslashes($cap->observaciones) }}',
                instructor_nombre:  '{{ addslashes($cap->instructor_nombre) }}',
                fecha_capacitacion: '{{ $cap->fecha_capacitacion?->format('Y-m-d\TH:i') }}',
                extender_link:      minutos > 0 ? parseInt(minutos) : null,
                sin_expiracion:     minutos == 0 ? true : null,
                cerrada:            false,
            }),
        });
        if (!res.ok) throw new Error();
        window.location.reload();
    } catch (e) {
        alert('Error al habilitar el link.');
    }
}

// ── Cerrar / Abrir ────────────────────────────────────────────
var csrfToken = '{{ csrf_token() }}';

async function cambiarEstado(accion) {
    var msg = accion === 'cerrar'
        ? '¿Cerrar la capacitación? Los asistentes ya no podrán registrarse hasta que la abras de nuevo.'
        : '¿Abrir la capacitación? Los asistentes podrán volver a registrarse (si el link sigue vigente).';

    if (!confirm(msg)) return;

    try {
        var res = await fetch('/admin/capacitaciones/{{ urlencode(Crypt::encryptString((string)$cap->id)) }}/' + accion, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error();
        window.location.reload();
    } catch (e) {
        alert('Error al cambiar el estado.');
    }
}

// ── Editar ────────────────────────────────────────────────────
var temasEdit = @json($cap->temas ?? []);

function renderTemasEdit() {
    var lista = document.getElementById('editTemasLista');
    lista.innerHTML = '';
    temasEdit.forEach(function (t, i) {
        var span = document.createElement('span');
        span.className = 'badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 px-2 py-1';
        span.style.fontSize = '13px';
        span.innerHTML = t + ' <button type="button" class="btn-close btn-close-sm" style="font-size:10px;" onclick="eliminarTemaEdit(' + i + ')"></button>';
        lista.appendChild(span);
    });
}

function agregarTemaEdit() {
    var input = document.getElementById('editTemaInput');
    var val = input.value.trim();
    if (!val) return;
    if (temasEdit.indexOf(val) === -1) { temasEdit.push(val); renderTemasEdit(); }
    input.value = '';
    input.focus();
}

function eliminarTemaEdit(i) {
    temasEdit.splice(i, 1);
    renderTemasEdit();
}

function abrirEditar() {
    renderTemasEdit();
    document.getElementById('editTemaInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); agregarTemaEdit(); }
    });
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

async function guardarEdicion() {
    var titulo        = document.getElementById('editTitulo').value.trim();
    var instructor    = document.getElementById('editInstructor').value.trim();
    var fecha         = document.getElementById('editFecha').value;
    var observaciones = document.getElementById('editObservaciones').value.trim();

    if (!titulo || !instructor || !fecha || !observaciones) {
        alert('Título, observaciones, instructor y fecha son obligatorios.');
        return;
    }

    var btn = document.getElementById('btnGuardarEdit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        var res = await fetch('/admin/capacitaciones/{{ urlencode(Crypt::encryptString((string)$cap->id)) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                titulo:             titulo,
                temas:              JSON.stringify(temasEdit),
                observaciones:      observaciones,
                instructor_nombre:  instructor,
                fecha_capacitacion: fecha,
                duracion_horas:     document.getElementById('editDuracion').value || null,
                cerrada:            document.getElementById('editCerrada').value === '1',
                extender_link:      document.getElementById('editExtenderLink').value || null,
            }),
        });

        if (!res.ok) throw new Error();

        bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
        window.location.reload();

    } catch (e) {
        alert('Error al guardar los cambios.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Guardar Cambios';
    }
}

function exportarCSV() {
    var rows = [['#','Cédula','Nombre','Correo','Estado','Fecha Confirmación']];
    if (dtAsistentes) {
        dtAsistentes.rows().every(function (i) {
            var tr = this.node();
            var cols = tr.querySelectorAll('td');
            rows.push([
                i + 1,
                cols[1].innerText.trim(),
                cols[2].innerText.trim(),
                cols[3].innerText.trim(),
                cols[4].innerText.trim(),
                cols[5].innerText.trim(),
            ]);
        });
    } else {
        document.querySelectorAll('#tablaAsistentes tbody tr').forEach(function (tr, i) {
            var cols = tr.querySelectorAll('td');
            rows.push([
                i + 1,
                cols[1].innerText.trim(),
                cols[2].innerText.trim(),
                cols[3].innerText.trim(),
                cols[4].innerText.trim(),
                cols[5].innerText.trim(),
            ]);
        });
    }

    var wb  = XLSX.utils.book_new();
    var ws  = XLSX.utils.aoa_to_sheet(rows);

    // Ancho de columnas
    ws['!cols'] = [
        { wch: 5 },  // #
        { wch: 14 }, // Cédula
        { wch: 35 }, // Nombre
        { wch: 30 }, // Correo
        { wch: 14 }, // Estado
        { wch: 20 }, // Fecha Confirmación
    ];

    XLSX.utils.book_append_sheet(wb, ws, 'Participantes');
    XLSX.writeFile(wb, 'asistentes_capacitacion_{{ $cap->id }}.xlsx');
}

// ── Agregar participante — modal ──────────────────────────────
var encryptedCapId    = '{{ urlencode(Crypt::encryptString((string)$cap->id)) }}';
var deptsCargados     = false;
var cedulasExistentes = @json($cap->asistentes->pluck('cedula')->filter()->values());

function abrirModalAgregar() {
    // Reset tabs al abrir
    var tabBuscar = document.querySelector('[data-bs-target="#tabBuscar"]');
    bootstrap.Tab.getOrCreateInstance(tabBuscar).show();
    document.getElementById('buscarQ').value     = '';
    document.getElementById('listaBuscar').innerHTML = '<p class="text-muted small text-center mt-3">Escribe para buscar empleados.</p>';
    document.getElementById('agregarCedula').value   = '';
    document.getElementById('agregarNombre').value   = '';
    document.getElementById('agregarCorreo').value   = '';
    document.getElementById('agregarTelefono').value = '';
    document.getElementById('agregarError').classList.add('d-none');
    document.getElementById('agregarSuccess').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalAgregar')).show();
}

function mostrarAgregarError(msg) {
    var el = document.getElementById('agregarError');
    el.textContent = msg;
    el.classList.remove('d-none');
    document.getElementById('agregarSuccess').classList.add('d-none');
}

function mostrarAgregarSuccess(msg) {
    var el = document.getElementById('agregarSuccess');
    el.textContent = msg;
    el.classList.remove('d-none');
    document.getElementById('agregarError').classList.add('d-none');
}

// ── Tab: Buscar empleado ──────────────────────────────────────
var buscarTimer = null;
var empleadosBuscar = [];

function buscarEmpleados() {
    clearTimeout(buscarTimer);
    buscarTimer = setTimeout(_ejecutarBusqueda, 350);
}

async function _ejecutarBusqueda() {
    var q = document.getElementById('buscarQ').value.trim();
    if (q.length < 2) {
        document.getElementById('listaBuscar').innerHTML = '<p class="text-muted small text-center mt-3">Escribe al menos 2 caracteres.</p>';
        return;
    }
    document.getElementById('listaBuscar').innerHTML = '<p class="text-muted small text-center mt-3"><span class="spinner-border spinner-border-sm"></span> Buscando...</p>';

    try {
        var res  = await fetch('/admin/capacitaciones/empleados?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
        var data = await res.json();
        empleadosBuscar = data.data || [];
        _renderListaBuscar();
    } catch (e) {
        document.getElementById('listaBuscar').innerHTML = '<p class="text-danger small text-center mt-3">Error al buscar.</p>';
    }
}

function _yaExiste(cedula) {
    return cedulasExistentes.indexOf(String(cedula)) !== -1;
}

function _renderListaBuscar() {
    var lista = document.getElementById('listaBuscar');
    if (!empleadosBuscar.length) {
        lista.innerHTML = '<p class="text-muted small text-center mt-3">Sin resultados.</p>';
        return;
    }
    var html = '<div class="list-group list-group-flush">';
    empleadosBuscar.forEach(function (emp, i) {
        var existe = _yaExiste(emp.cedula);
        html += '<label class="list-group-item ' + (existe ? '' : 'list-group-item-action') + ' d-flex align-items-center gap-3 py-2 px-2" style="' + (existe ? 'opacity:.6;' : 'cursor:pointer;') + '">'
            + '<input type="checkbox" class="form-check-input flex-shrink-0" value="' + i + '" onchange="_actualizarBtnBuscar()" ' + (existe ? 'disabled' : '') + '>'
            + '<div>'
            + '<div class="fw-semibold small">' + emp.nombre
            + (existe ? ' <span class="badge bg-secondary ms-1" style="font-size:10px;">Ya programado</span>' : '')
            + '</div>'
            + '<div class="text-muted" style="font-size:12px;">' + (emp.cedula || '—') + ' · ' + (emp.correo || '—') + '</div>'
            + '</div></label>';
    });
    html += '</div>';
    lista.innerHTML = html;
}

function _actualizarBtnBuscar() {
    var chk = document.querySelectorAll('#listaBuscar input[type=checkbox]:checked');
    var btn = document.getElementById('btnAgregarBuscar');
    btn.style.display = chk.length > 0 ? 'inline-block' : 'none';
}

async function agregarSeleccionadosBuscar(btn) {
    var chk = document.querySelectorAll('#listaBuscar input[type=checkbox]:checked');
    var seleccionados = Array.from(chk).map(function (c) { return empleadosBuscar[parseInt(c.value)]; });
    if (!seleccionados.length) return;
    await _enviarBulk(seleccionados, btn);
}

// ── Tab: Por Área ─────────────────────────────────────────────
var empleadosArea = [];

async function cargarDepartamentos() {
    if (deptsCargados) return;
    try {
        var res  = await fetch('/admin/capacitaciones/departamentos', { headers: { 'Accept': 'application/json' } });
        var data = await res.json();
        var sel  = document.getElementById('selectDepartamento');
        (data.data || []).forEach(function (d) {
            var opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.nombre;
            sel.appendChild(opt);
        });
        deptsCargados = true;
    } catch (e) { /* silencioso */ }
}

async function cargarEmpleadosPorArea() {
    var deptId = document.getElementById('selectDepartamento').value;
    var lista  = document.getElementById('listaArea');
    var panel  = document.getElementById('panelAreaAcciones');

    if (!deptId) { lista.innerHTML = ''; panel.style.display = 'none'; return; }

    lista.innerHTML = '<p class="text-muted small text-center mt-3"><span class="spinner-border spinner-border-sm"></span> Cargando...</p>';
    panel.style.display = 'none';

    try {
        var url = deptId === 'all'
            ? '/admin/capacitaciones/empleados'
            : '/admin/capacitaciones/empleados?departamento_id=' + deptId;
        var res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
        var data = await res.json();
        empleadosArea = data.data || [];
        _renderListaArea();
    } catch (e) {
        lista.innerHTML = '<p class="text-danger small text-center mt-3">Error al cargar.</p>';
    }
}

function _renderListaArea() {
    var lista = document.getElementById('listaArea');
    var panel = document.getElementById('panelAreaAcciones');

    if (!empleadosArea.length) {
        lista.innerHTML = '<p class="text-muted small text-center mt-3">Sin empleados en esta área.</p>';
        panel.style.display = 'none';
        return;
    }

    var html = '<div class="list-group list-group-flush">';
    html += '<label class="list-group-item d-flex align-items-center gap-3 py-2 px-2 bg-light" style="cursor:pointer;">'
        + '<input type="checkbox" id="chkTodosArea" class="form-check-input" onchange="_toggleTodosArea(this)">'
        + '<span class="small fw-semibold">Seleccionar todos</span></label>';
    empleadosArea.forEach(function (emp, i) {
        var existe = _yaExiste(emp.cedula);
        html += '<label class="list-group-item ' + (existe ? '' : 'list-group-item-action') + ' d-flex align-items-center gap-3 py-2 px-2" style="' + (existe ? 'opacity:.6;' : 'cursor:pointer;') + '">'
            + '<input type="checkbox" class="form-check-input chk-area" value="' + i + '" onchange="_actualizarCntArea()" ' + (existe ? 'disabled' : '') + '>'
            + '<div>'
            + '<div class="fw-semibold small">' + emp.nombre
            + (existe ? ' <span class="badge bg-secondary ms-1" style="font-size:10px;">Ya programado</span>' : '')
            + '</div>'
            + '<div class="text-muted" style="font-size:12px;">' + (emp.cedula || '—') + ' · ' + (emp.correo || '—') + '</div>'
            + '</div></label>';
    });
    html += '</div>';
    lista.innerHTML = html;
    panel.style.display = 'flex';
    document.getElementById('cntArea').textContent = '0';
}

function _toggleTodosArea(chk) {
    document.querySelectorAll('#listaArea .chk-area').forEach(function (c) { c.checked = chk.checked; });
    _actualizarCntArea();
}

function _actualizarCntArea() {
    var cnt = document.querySelectorAll('#listaArea .chk-area:checked').length;
    document.getElementById('cntArea').textContent = cnt;
    var todos = document.querySelectorAll('#listaArea .chk-area').length;
    var chkTodos = document.getElementById('chkTodosArea');
    if (chkTodos) chkTodos.checked = cnt === todos && todos > 0;
}

async function agregarSeleccionadosArea(btn) {
    var chk = document.querySelectorAll('#listaArea .chk-area:checked');
    var seleccionados = Array.from(chk).map(function (c) { return empleadosArea[parseInt(c.value)]; });
    if (!seleccionados.length) { mostrarAgregarError('Selecciona al menos un empleado.'); return; }
    await _enviarBulk(seleccionados, btn);
}

// ── Bulk send ─────────────────────────────────────────────────
async function _enviarBulk(lista, btn) {
    document.getElementById('agregarError').classList.add('d-none');
    document.getElementById('agregarSuccess').classList.add('d-none');

    var originalHtml = null;
    if (btn) {
        originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Agregando...';
    }

    try {
        var res  = await fetch('/admin/capacitaciones/' + encryptedCapId + '/participantes/bulk', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ participantes: lista }),
        });
        var data = await res.json();

        if (!res.ok) { mostrarAgregarError(data.message || 'Error al agregar.'); return; }

        var msg = data.agregados + ' participante(s) agregado(s)';
        if (data.duplicados > 0) msg += ' · ' + data.duplicados + ' ya existían (ignorados)';
        mostrarAgregarSuccess(msg + '. La página se actualizará.');
        setTimeout(function () { window.location.reload(); }, 1800);

    } catch (e) {
        mostrarAgregarError('Error de conexión.');
    } finally {
        if (btn && originalHtml) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
}

// ── Tab: Manual ───────────────────────────────────────────────
async function guardarParticipante() {
    var cedula   = document.getElementById('agregarCedula').value.trim();
    var nombre   = document.getElementById('agregarNombre').value.trim();
    var correo   = document.getElementById('agregarCorreo').value.trim();
    var telefono = document.getElementById('agregarTelefono').value.trim();

    if (!cedula || !nombre) { mostrarAgregarError('Cédula y nombre son obligatorios.'); return; }

    var btn = document.getElementById('btnGuardarParticipante');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
    document.getElementById('agregarError').classList.add('d-none');

    try {
        var res  = await fetch('/admin/capacitaciones/' + encryptedCapId + '/participantes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ cedula, nombre, correo, telefono }),
        });
        var data = await res.json();

        if (!res.ok) { mostrarAgregarError(data.message || 'Error al agregar el participante.'); return; }

        mostrarAgregarSuccess('Participante agregado. La página se actualizará.');
        setTimeout(function () { window.location.reload(); }, 1500);

    } catch (e) {
        mostrarAgregarError('Error de conexión.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-user-plus me-1"></i> Agregar';
    }
}

// ── Eliminar participante ─────────────────────────────────────
async function eliminarParticipante(id) {
    var result = await Swal.fire({
        title: '¿Quitar participante?',
        text: 'Esta acción lo removerá de la capacitación.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#e74c3c',
        reverseButtons: true,
    });
    if (!result.isConfirmed) return;
    try {
        var res = await fetch('/admin/capacitaciones/{{ urlencode(Crypt::encryptString((string)$cap->id)) }}/participantes/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error();
        var fila = document.getElementById('fila-asistente-' + id);
        if (fila) {
            if (dtAsistentes) {
                dtAsistentes.row(fila).remove().draw();
            } else {
                fila.remove();
            }
        }
    } catch (e) {
        alert('Error al eliminar el participante.');
    }
}
</script>
@endpush
