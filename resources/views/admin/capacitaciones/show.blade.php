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
                            Asistentes Registrados
                            <span class="badge bg-primary ms-1">{{ $cap->asistentes->count() }}</span>
                        </h6>
                        @if($cap->asistentes->count() > 0)
                            <button onclick="exportarCSV()" class="btn btn-sm btn-outline-success">
                                <i class="ti ti-file-export me-1"></i> Exportar CSV
                            </button>
                        @endif
                    </div>

                    @if($cap->asistentes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaAsistentes">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cap->asistentes as $i => $asistente)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $asistente->nombre }}</td>
                                    <td>{{ $asistente->correo }}</td>
                                    <td>{{ $asistente->telefono ?? '—' }}</td>
                                    <td>{{ $asistente->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="ti ti-users" style="font-size:48px;opacity:.3;"></i>
                            <p class="mt-2 mb-0">Aún no hay asistentes registrados.</p>
                            <p class="small">Comparte el link para que los participantes se registren.</p>
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

@push('scripts')
<script>
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
    var rows = [['#','Nombre','Correo','Teléfono','Fecha Registro']];
    document.querySelectorAll('#tablaAsistentes tbody tr').forEach(function (tr) {
        var cols = tr.querySelectorAll('td');
        rows.push([cols[0].innerText, cols[1].innerText, cols[2].innerText, cols[3].innerText, cols[4].innerText]);
    });
    var csv  = rows.map(function (r) { return r.map(function (c) { return '"' + c.replace(/"/g,'""') + '"'; }).join(','); }).join('\n');
    var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    var a    = document.createElement('a');
    a.href   = URL.createObjectURL(blob);
    a.download = 'asistentes_capacitacion_{{ $cap->id }}.csv';
    a.click();
}
</script>
@endpush
