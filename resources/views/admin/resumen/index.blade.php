@extends('layouts.admin')
@section('title', 'Resumen Marcación')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Resumen Marcación</h4>
                    <p class="text-muted mb-0">Entradas y salidas agrupadas por empleado y día</p>
                </div>
                <div>
                    @if(auth()->user()->role !== 'empleado')
                    <button class="btn btn-primary btn-sm me-2" onclick="abrirModalManual()">
                        <i class="fa-solid fa-plus me-1"></i> Registro Manual
                    </button>
                    @endif
                    <button class="btn btn-success btn-sm" onclick="exportar()">
                        <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body p-2">
            <div class="row g-2 mb-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Buscar...">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Desde</label>
                    <input type="date" id="dateFrom" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Hasta</label>
                    <input type="date" id="dateTo" class="form-control form-control-sm">
                </div>
                <div class="col-md-auto d-flex align-items-end gap-2">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosExtraRes" title="Más filtros">
                        <i class="fa-solid fa-sliders"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="cargarResumen()">
                        <i class="fa-solid fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="collapse" id="filtrosExtraRes">
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <select id="filterEmpleado" class="form-select form-select-sm">
                            <option value="">Todos los empleados</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterDepto" class="form-select form-select-sm">
                            <option value="">Todos los departamentos</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla resumen --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0 w-100" id="resumenTable">
                <thead class="table-light">
                    <tr>
                        <th class="col-res-empleado">Empleado</th>
                        <th class="col-res-codigo">Código</th>
                        <th class="col-res-depto">Departamento</th>
                        <th class="col-res-fecha">Fecha</th>
                        <th class="col-res-e1">Entrada 1</th>
                        <th class="col-res-s1">Salida 1</th>
                        <th class="col-res-e2">Entrada 2</th>
                        <th class="col-res-s2">Salida 2</th>
                        <th class="col-res-e3">Entrada 3</th>
                        <th class="col-res-s3">Salida 3</th>
                        <th class="col-res-e4">Entrada 4</th>
                        <th class="col-res-s4">Salida 4</th>
                        <th class="col-res-total text-end">Total Horas</th>
                        <th class="col-res-acciones text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="resumenTbody">
                    <tr id="trLoadingRes">
                        <td colspan="14" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                            <p class="text-muted mt-2 mb-0 small">Cargando registros...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Editar Tipo --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Editar Registro</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3">
                    <label class="form-label">Empleado</label>
                    <input type="text" id="editEmpleado" class="form-control form-control-sm" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sede</label>
                    <select id="editSede" class="form-select form-select-sm">
                        <option value="">Seleccionar sede...</option>
                        @foreach($sedes as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha y hora <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="editFechaHora" class="form-control form-control-sm">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select id="editTipo" class="form-select form-select-sm">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones <span class="text-danger">*</span></label>
                    <textarea id="editObservacion" class="form-control form-control-sm" rows="2"
                        placeholder="Observación sobre este registro..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="guardarEdicion()">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Registro Manual --}}
<div class="modal fade" id="modalManual" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Crear Registro Manual</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Empleado <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="text" id="manualEmpleadoInput" class="form-control form-control-sm"
                            placeholder="Buscar por nombre o código..." autocomplete="off"
                            oninput="filtrarEmpleados(this.value)">
                        <input type="hidden" id="manualEmpleado">
                        <div id="manualEmpleadoDropdown"
                            class="d-none position-absolute w-100 bg-white border rounded shadow-sm"
                            style="z-index:1060;max-height:200px;overflow-y:auto;top:100%"></div>
                    </div>
                    <div id="manualEmpleadoSeleccionado" class="form-text text-success d-none"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sede <span class="text-danger">*</span></label>
                    <select id="manualSede" class="form-select form-select-sm" required>
                        <option value="">Seleccionar sede...</option>
                        @foreach($sedes as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select id="manualTipo" class="form-select form-select-sm">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="manualFecha" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hora <span class="text-danger">*</span></label>
                        <input type="time" id="manualHora" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones <span class="text-danger">*</span></label>
                    <textarea id="manualObservacion" class="form-control form-control-sm"
                        rows="2" placeholder="Motivo del registro manual u observación..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="guardarManual()">Crear Registro</button>
            </div>
        </div>
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

{{-- Botón flotante visibilidad de columnas --}}
<div id="colVisBtnRes" title="Mostrar / ocultar columnas"
     style="position:fixed;bottom:70px;right:28px;z-index:1055;cursor:pointer;
            width:48px;height:48px;border-radius:50%;background:#1ab394;
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 4px 14px rgba(0,0,0,.25);transition:background .2s;"
     onmouseenter="this.style.background='#17a07d'" onmouseleave="this.style.background='#1ab394'"
     onclick="toggleColVisPanelRes()">
    <i class="ti ti-settings text-white" style="font-size:22px;"></i>
</div>

<div id="colVisPanelRes"
     style="display:none;position:fixed;bottom:128px;right:28px;z-index:1056;
            background:#fff;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.18);
            min-width:220px;padding:14px 16px;">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="fw-semibold small">Columnas visibles</span>
        <button class="btn btn-link btn-sm p-0 text-muted" onclick="toggleColVisPanelRes()">
            <i class="ti ti-x"></i>
        </button>
    </div>
    <div id="colVisChecksRes"></div>
    <div class="mt-2 pt-2 border-top d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="colVisResTodos(true)">Todos</button>
        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="colVisResTodos(false)">Ninguno</button>
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
#resumenTable th, #resumenTable td {
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
const isAdmin    = {{ auth()->user()->role === 'admin'    ? 'true' : 'false' }};
const myUserId   = {{ auth()->id() }};
let deptoMap = {};
let allRegistros = [];

// ── Inicialización ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const hoy   = new Date().toISOString().slice(0, 10);
    const lunes = inicioSemana();
    document.getElementById('dateFrom').value = lunes;
    document.getElementById('dateTo').value   = hoy;

    if (isEmpleado) {
        document.getElementById('filterEmpleado').closest('.col-md-3').style.display = 'none';
        document.getElementById('filterDepto').closest('.col-md-3').style.display    = 'none';
    }

    cargarFiltros();
    cargarResumen();

    // Cargar empleados al expandir filtros extra
    document.getElementById('filtrosExtraRes').addEventListener('show.bs.collapse', function() {
        cargarEmpleadosParaModal();
    });
    colVisResApply(colVisResGetState());
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#colVisPanelRes, #colVisBtnRes')) {
            document.getElementById('colVisPanelRes').style.display = 'none';
        }
    });
});

function inicioSemana() {
    const d = new Date();
    const day = d.getDay() || 7;
    d.setDate(d.getDate() - day + 1);
    return d.toISOString().slice(0, 10);
}

let _filtrosCargados = false;

async function cargarFiltros() {
    // Solo catalogos (departamentos) al inicio — los empleados se cargan al abrir el modal
    const resCat = await fetch('/admin/catalogos', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
    const dataCat = await resCat.json();

    const deptos = dataCat.departamentos || [];
    deptoMap = Object.fromEntries(deptos.map(d => [d.id, d.nombre]));

    const selD = document.getElementById('filterDepto');
    deptos.forEach(d => {
        selD.innerHTML += `<option value="${d.id}">${d.nombre}</option>`;
    });
}

async function cargarEmpleadosParaModal() {
    if (_filtrosCargados) return;
    _filtrosCargados = true;

    const resE = await fetch('/admin/empleados/list?per_page=500&fields=id,name,codigo_empleado,departamento_id', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });
    const dataE = await resE.json();
    const empleados = dataE.data || [];
    window._empleadosList = empleados;

    const selE = document.getElementById('filterEmpleado');
    empleados.forEach(e => {
        selE.innerHTML += `<option value="${e.id}">${e.name} (${e.codigo_empleado || ''})</option>`;
    });
}

// ── Cargar resumen ────────────────────────────────────────────────────────────
async function cargarResumen() {
    const from    = document.getElementById('dateFrom').value;
    const to      = document.getElementById('dateTo').value;
    const userId  = isEmpleado ? myUserId : document.getElementById('filterEmpleado').value;
    const deptoId = isEmpleado ? ''       : document.getElementById('filterDepto').value;

    if (!from || !to) { alert('Selecciona el rango de fechas.'); return; }

    let url = `/admin/resumen/records?date_from=${from}&date_to=${to}`;
    if (userId)  url += `&user_id=${userId}`;

    const tbody = document.getElementById('resumenTbody');
    if ($.fn.DataTable.isDataTable('#resumenTable')) {
        $('#resumenTable').DataTable().destroy();
    }
    tbody.innerHTML = '<tr id="trLoadingRes"><td colspan="14" class="text-center py-5"><div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div><p class="text-muted mt-2 mb-0 small">Cargando registros...</p></td></tr>';

    try {
        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || `HTTP ${res.status}`);
        }
        const data = await res.json();
        let registros = data.data || [];

        if (deptoId) {
            registros = registros.filter(r => r.user?.departamento_id == deptoId);
        }

        const search = document.getElementById('filterSearch').value.trim().toLowerCase();
        if (search) {
            registros = registros.filter(r =>
                (r.user?.name || '').toLowerCase().includes(search) ||
                (r.user?.cedula || '').toLowerCase().includes(search) ||
                (r.user?.codigo_empleado || '').toLowerCase().includes(search)
            );
        }

        allRegistros = registros;

        if (!registros.length) {
            tbody.innerHTML = '<tr><td colspan="14" class="text-center text-muted py-4">Sin registros para el período seleccionado</td></tr>';
            document.getElementById('resumenInfo').textContent  = '';
            document.getElementById('resumenTotal').textContent = '';
            return;
        }

        // Agrupar por empleado + fecha
        const grupos = {};
        registros.forEach(r => {
            const fecha = r.fecha_hora.slice(0, 10);
            const key   = `${r.user_id}_${fecha}`;
            if (!grupos[key]) grupos[key] = { user: r.user, fecha, thumbnail: r.foto_perfil_thumbnail, registros: [] };
            grupos[key].registros.push(r);
        });

        let filas = '';
        let totalMinGlobal = 0;
        let totalDias      = 0;

        Object.values(grupos).sort((a, b) => {
            const na = a.user?.name ?? '';
            const nb = b.user?.name ?? '';
            return na.localeCompare(nb) || a.fecha.localeCompare(b.fecha);
        }).forEach(g => {
            const sorted = g.registros.sort((a, b) => a.fecha_hora.localeCompare(b.fecha_hora));

            const sessions = [];
            let openEntrada = null;
            for (const r of sorted) {
                if (r.tipo === 'entrada') {
                    if (openEntrada) sessions.push({ e: openEntrada, s: null });
                    openEntrada = r;
                } else if (r.tipo === 'salida') {
                    sessions.push({ e: openEntrada, s: r });
                    openEntrada = null;
                }
            }
            if (openEntrada) sessions.push({ e: openEntrada, s: null });

            const toDate = str => new Date(str.replace(' ', 'T'));
            let totalMin = 0;
            for (const s of sessions) {
                if (s.e && s.s) {
                    totalMin += Math.round((toDate(s.s.fecha_hora) - toDate(s.e.fecha_hora)) / 60000);
                }
            }

            // Descuento de almuerzo usando el día específico de cada sesión
            for (const s of sessions) {
                if (s.e && s.s) {
                    const fechaEntrada = toDate(s.e.fecha_hora);
                    const isoDay = fechaEntrada.getDay() === 0 ? 7 : fechaEntrada.getDay();
                    const horario = s.e.horario;
                    const dia = (horario?.dias || []).find(d => d.dia_semana === isoDay);
                    if (dia?.duracion_almuerzo_min) {
                        const durMin = Math.round((toDate(s.s.fecha_hora) - fechaEntrada) / 60000);
                        if (durMin > dia.duracion_almuerzo_min) {
                            totalMin -= dia.duracion_almuerzo_min;
                        }
                    }
                }
            }

            // Construir celdas clickeables (hasta 4 pares entrada/salida)
            const fmtHora = str => toDate(str).toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'America/Bogota'});
            const celdas = [];
            for (let i = 0; i < 4; i++) {
                const s = sessions[i];
                if (s?.e) {
                    const penE = isAdmin ? ` onclick="editarRegistro(${s.e.id})" title="Click para editar" style="cursor:pointer"` : '';
                    const iconE = isAdmin ? ' <i class="fa-solid fa-pen fa-xs text-muted"></i>' : '';
                    celdas.push(`<span class="text-success fw-semibold"${penE}>${fmtHora(s.e.fecha_hora)}${iconE}</span>`);
                } else {
                    celdas.push('<span class="text-muted">—</span>');
                }
                if (s?.s) {
                    const penS = isAdmin ? ` onclick="editarRegistro(${s.s.id})" title="Click para editar" style="cursor:pointer"` : '';
                    const iconS = isAdmin ? ' <i class="fa-solid fa-pen fa-xs text-muted"></i>' : '';
                    celdas.push(`<span class="text-danger fw-semibold"${penS}>${fmtHora(s.s.fecha_hora)}${iconS}</span>`);
                } else {
                    celdas.push('<span class="text-muted">—</span>');
                }
            }

            totalMinGlobal += totalMin;
            totalDias++;

            const totalStr = totalMin > 0
                ? `<strong>${Math.floor(totalMin/60)}h ${String(totalMin%60).padStart(2,'0')}m</strong>`
                : '<span class="text-muted">—</span>';

            const deptoNombre = deptoMap[g.user?.departamento_id] || g.user?.departamento || '—';
            const fechaFmt    = g.fecha.split('-').reverse().join('/');

            // Botón para agregar registro en ese día para ese usuario (solo admin/supervisor)
            const btnAdd = isEmpleado ? '' : `<button class="btn btn-outline-primary btn-sm py-0 px-1" onclick="abrirModalManualPre(${g.user?.id}, '${g.fecha}')" title="Agregar registro"><i class="fa-solid fa-plus fa-xs"></i></button>`;

            const colCls = ['col-res-e1','col-res-s1','col-res-e2','col-res-s2','col-res-e3','col-res-s3','col-res-e4','col-res-s4'];
            const nombreEsc = (g.user?.name ?? '').replace(/'/g, "\\'");
            const avatarHtml = g.thumbnail
                ? `<img src="${g.thumbnail}" onclick="verFotoPerfil('${nombreEsc}','${g.thumbnail}',${g.user?.id})" style="width:32px;height:32px;object-fit:cover;border-radius:50%;border:2px solid #1ab394;flex-shrink:0;cursor:pointer;" title="Ver foto de perfil" alt="">`
                : `<span style="width:32px;height:32px;border-radius:50%;background:#e9ecef;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-user text-muted" style="font-size:14px;"></i></span>`;

            filas += `<tr>
                <td class="col-res-empleado"><div class="d-flex align-items-center gap-2">${avatarHtml}<span>${g.user?.name ?? 'N/A'}</span></div></td>
                <td class="col-res-codigo"><span class="badge bg-primary">${g.user?.codigo_empleado ?? '—'}</span></td>
                <td class="col-res-depto"><small class="text-muted">${deptoNombre}</small></td>
                <td class="col-res-fecha">${fechaFmt}</td>
                ${celdas.map((c, i) => `<td class="${colCls[i]}">${c}</td>`).join('')}
                <td class="col-res-total text-end">${totalStr}</td>
                <td class="col-res-acciones text-center">${btnAdd}</td>
            </tr>`;
        });

        tbody.innerHTML = filas;

        $('#resumenTable').DataTable({
            paging:    true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            ordering:  false,
            scrollX:   true,
            language: {
                lengthMenu: 'Mostrar _MENU_ registros',
                zeroRecords: 'Sin registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros)',
                search: 'Buscar:',
                paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
            },
            initComplete: function() {
                $('#resumenTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                $('#resumenTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                $('#resumenTable_filter').prepend(
                    '<button class="btn btn-sm btn-outline-secondary me-2" onclick="cargarResumen()" title="Recargar tabla"><i class="ti ti-refresh"></i></button>'
                );
            },
        });

        colVisResApply(colVisResGetState());


    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="14" class="text-center text-danger py-3">Error al cargar datos: ${e.message}</td></tr>`;
        console.error('cargarResumen error:', e);
    }
}

// ── Editar registro (cambiar tipo) ───────────────────────────────────────────
function editarRegistro(id) {
    const reg = allRegistros.find(r => r.id === id);
    if (!reg) return;

    // Convertir ISO a formato datetime-local (YYYY-MM-DDTHH:mm) en hora Colombia
    const dt = new Date(reg.fecha_hora);
    const local = new Date(dt.getTime() - dt.getTimezoneOffset() * 60000)
        .toISOString().slice(0, 16);

    document.getElementById('editId').value          = id;
    document.getElementById('editEmpleado').value    = reg.user?.name || 'N/A';
    document.getElementById('editSede').value        = reg.sede_id  ?? '';
    document.getElementById('editFechaHora').value   = local;
    document.getElementById('editTipo').value        = reg.tipo;
    document.getElementById('editObservacion').value = reg.observacion ?? '';

    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

async function guardarEdicion() {
    const id          = document.getElementById('editId').value;
    const tipo        = document.getElementById('editTipo').value;
    const fechaHora   = document.getElementById('editFechaHora').value;
    const sedeId      = document.getElementById('editSede').value;
    const observacion = document.getElementById('editObservacion').value.trim();

    if (!fechaHora || !observacion) { alert('Completa todos los campos obligatorios.'); return; }

    try {
        const res = await fetch(`/admin/attendance/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                tipo,
                fecha_hora:   fechaHora.replace('T', ' ') + ':00',
                sede_id:      sedeId    || null,
                observacion:  observacion || null,
            }),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || `HTTP ${res.status}`);
        }

        bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
        cargarResumen();
    } catch(e) {
        alert('Error al guardar: ' + e.message);
    }
}

// ── Autocomplete empleados ────────────────────────────────────────────────────
function filtrarEmpleados(query) {
    const dropdown = document.getElementById('manualEmpleadoDropdown');
    const hidden   = document.getElementById('manualEmpleado');
    const info     = document.getElementById('manualEmpleadoSeleccionado');

    // Al escribir, limpiar selección previa
    hidden.value = '';
    info.classList.add('d-none');

    const lista = window._empleadosList || [];
    const q = query.trim().toLowerCase();

    if (!q) { dropdown.classList.add('d-none'); return; }

    const coinciden = lista.filter(e =>
        e.name.toLowerCase().includes(q) ||
        (e.codigo_empleado || '').toLowerCase().includes(q)
    ).slice(0, 10);

    if (!coinciden.length) { dropdown.classList.add('d-none'); return; }

    dropdown.innerHTML = coinciden.map(e => `
        <div class="px-3 py-2 small" style="cursor:pointer"
            onmousedown="seleccionarEmpleado(${e.id}, '${e.name.replace(/'/g,"\\'")} (${e.codigo_empleado || ''})')"
            onmouseover="this.style.background='#f0f4ff'"
            onmouseout="this.style.background=''">
            <strong>${e.name}</strong>
            <span class="text-muted ms-1">${e.codigo_empleado || ''}</span>
        </div>`).join('');
    dropdown.classList.remove('d-none');
}

function seleccionarEmpleado(id, label) {
    document.getElementById('manualEmpleado').value        = id;
    document.getElementById('manualEmpleadoInput').value   = label;
    document.getElementById('manualEmpleadoDropdown').classList.add('d-none');
    const info = document.getElementById('manualEmpleadoSeleccionado');
    info.textContent = '✓ ' + label;
    info.classList.remove('d-none');
}

function resetAutocomplete() {
    document.getElementById('manualEmpleado').value      = '';
    document.getElementById('manualEmpleadoInput').value = '';
    document.getElementById('manualEmpleadoDropdown').classList.add('d-none');
    document.getElementById('manualEmpleadoSeleccionado').classList.add('d-none');
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', e => {
    if (!e.target.closest('#manualEmpleadoInput') && !e.target.closest('#manualEmpleadoDropdown')) {
        document.getElementById('manualEmpleadoDropdown')?.classList.add('d-none');
    }
});

// ── Registro Manual ──────────────────────────────────────────────────────────
async function abrirModalManual() {
    await cargarEmpleadosParaModal();
    resetAutocomplete();
    document.getElementById('manualSede').value = '';
    document.getElementById('manualTipo').value = 'entrada';
    document.getElementById('manualFecha').value = new Date().toISOString().slice(0, 10);
    document.getElementById('manualHora').value = '';
    document.getElementById('manualObservacion').value = '';
    new bootstrap.Modal(document.getElementById('modalManual')).show();
}

async function abrirModalManualPre(userId, fecha) {
    await cargarEmpleadosParaModal();
    resetAutocomplete();
    const emp = (window._empleadosList || []).find(e => e.id == userId);
    if (emp) seleccionarEmpleado(emp.id, `${emp.name} (${emp.codigo_empleado || ''})`);
    document.getElementById('manualSede').value = '';
    document.getElementById('manualTipo').value = 'entrada';
    document.getElementById('manualFecha').value = fecha;
    document.getElementById('manualHora').value = '';
    document.getElementById('manualObservacion').value = '';
    new bootstrap.Modal(document.getElementById('modalManual')).show();
}

async function guardarManual() {
    const userId      = document.getElementById('manualEmpleado').value;
    const sedeId      = document.getElementById('manualSede').value;
    const tipo        = document.getElementById('manualTipo').value;
    const fecha       = document.getElementById('manualFecha').value;
    const hora        = document.getElementById('manualHora').value;
    const observacion = document.getElementById('manualObservacion').value.trim();

    if (!userId || !sedeId || !fecha || !hora || !observacion) {
        alert('Completa todos los campos obligatorios.');
        return;
    }

    const fechaHora = `${fecha} ${hora}:00`;

    try {
        const res = await fetch('/admin/attendance/manual', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ user_id: parseInt(userId), sede_id: parseInt(sedeId), tipo, fecha_hora: fechaHora, observacion: observacion || null }),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || `HTTP ${res.status}`);
        }

        bootstrap.Modal.getInstance(document.getElementById('modalManual')).hide();
        cargarResumen();
    } catch(e) {
        alert('Error al crear registro: ' + e.message);
    }
}

// ── Visibilidad de columnas (resumen) ─────────────────────────────────────────
var COL_VIS_KEY_RES = 'resumen_col_vis';
var COL_VIS_DEFS_RES = [
    { cls: 'col-res-empleado',  label: 'Empleado',    default: true  },
    { cls: 'col-res-codigo',    label: 'Código',       default: true  },
    { cls: 'col-res-depto',     label: 'Departamento', default: false },
    { cls: 'col-res-fecha',     label: 'Fecha',        default: true  },
    { cls: 'col-res-e1',        label: 'Entrada 1',    default: true  },
    { cls: 'col-res-s1',        label: 'Salida 1',     default: true  },
    { cls: 'col-res-e2',        label: 'Entrada 2',    default: true  },
    { cls: 'col-res-s2',        label: 'Salida 2',     default: true  },
    { cls: 'col-res-e3',        label: 'Entrada 3',    default: false },
    { cls: 'col-res-s3',        label: 'Salida 3',     default: false },
    { cls: 'col-res-e4',        label: 'Entrada 4',    default: false },
    { cls: 'col-res-s4',        label: 'Salida 4',     default: false },
    { cls: 'col-res-total',     label: 'Total Horas',  default: true  },
    { cls: 'col-res-acciones',  label: 'Acciones',     default: true  },
];

function colVisResGetState() {
    try {
        var stored = localStorage.getItem(COL_VIS_KEY_RES);
        if (stored) return JSON.parse(stored);
    } catch(e) {}
    var state = {};
    COL_VIS_DEFS_RES.forEach(function(c) { state[c.cls] = c.default; });
    return state;
}

function colVisResSaveState(state) {
    localStorage.setItem(COL_VIS_KEY_RES, JSON.stringify(state));
}

function colVisResApply(state) {
    COL_VIS_DEFS_RES.forEach(function(c) {
        var visible = !!state[c.cls];
        document.querySelectorAll('.' + c.cls).forEach(function(el) {
            el.style.display = visible ? '' : 'none';
        });
    });
}

function colVisResBuildPanel() {
    var state = colVisResGetState();
    var container = document.getElementById('colVisChecksRes');
    container.innerHTML = '';
    COL_VIS_DEFS_RES.forEach(function(c) {
        var checked = state[c.cls] ? 'checked' : '';
        var div = document.createElement('div');
        div.className = 'form-check form-switch mb-1';
        div.innerHTML =
            '<input class="form-check-input" type="checkbox" id="colvisRes_' + c.cls + '" ' + checked + ' onchange="colVisResToggle(\'' + c.cls + '\', this.checked)">' +
            '<label class="form-check-label small" for="colvisRes_' + c.cls + '">' + c.label + '</label>';
        container.appendChild(div);
    });
}

function colVisResToggle(cls, visible) {
    var state = colVisResGetState();
    state[cls] = visible;
    colVisResSaveState(state);
    colVisResApply(state);
}

function colVisResTodos(visible) {
    var state = colVisResGetState();
    COL_VIS_DEFS_RES.forEach(function(c) { state[c.cls] = visible; });
    colVisResSaveState(state);
    colVisResApply(state);
    COL_VIS_DEFS_RES.forEach(function(c) {
        var el = document.getElementById('colvisRes_' + c.cls);
        if (el) el.checked = visible;
    });
}

function toggleColVisPanelRes() {
    var panel = document.getElementById('colVisPanelRes');
    if (panel.style.display === 'none') {
        colVisResBuildPanel();
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

// ── Foto de perfil ────────────────────────────────────────────────────────────
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

// ── Exportar CSV ──────────────────────────────────────────────────────────────
function exportar() {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    if (!from || !to) { alert('Selecciona el rango de fechas primero.'); return; }
    const userId = document.getElementById('filterEmpleado').value;
    let url = `/admin/reports/export?date_from=${from}&date_to=${to}`;
    if (userId) url += `&user_id=${userId}`;
    window.location.href = url;
}
</script>
<style>
.cursor-pointer { cursor: pointer; }
.cursor-pointer:hover { opacity: 0.7; }
</style>
@endpush
