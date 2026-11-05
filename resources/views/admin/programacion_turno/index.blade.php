@extends('layouts.admin')
@section('title', 'Programación de Turno')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-calendar-week me-2 text-primary"></i>Programación de Turno</h4>
                <p class="text-muted mb-0">
                    Semana del
                    <strong>{{ $semanaInicio->locale('es')->isoFormat('D [de] MMMM') }}</strong>
                    al
                    <strong>{{ $semanaFin->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</strong>
                </p>
            </div>
            @can('programacion_turno.crear')
            <button class="btn btn-primary" onclick="guardarProgramacion()">
                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar programación
            </button>
            @else
            <button class="btn btn-primary" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;">
                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar programación
            </button>
            @endcan
        </div>
    </div>

    {{-- Alerta informativa --}}
    <div class="alert alert-info d-flex align-items-start gap-2 py-2 mb-3">
        <i class="fa-solid fa-circle-info mt-1 flex-shrink-0"></i>
        <span>
            Selecciona el turno para cada empleado. Los cambios se aplicarán automáticamente el
            <strong>martes {{ $semanaInicio->locale('es')->isoFormat('D [de] MMMM') }}</strong>
            a la hora configurada en la empresa.
        </span>
    </div>

    {{-- Filtro + Asignación masiva --}}
    @can('programacion_turno.crear')
    <div class="card border-0 bg-light mb-3">
        <div class="card-body py-2 px-3 d-flex align-items-center gap-3 flex-wrap">

            {{-- Filtro por turno actual --}}
            <span class="text-muted small fw-semibold text-nowrap">
                <i class="fa-solid fa-filter me-1"></i> Filtrar por turno actual:
            </span>
            <select id="selFiltro" class="form-select form-select-sm" style="max-width:250px" onchange="filtrarPorTurno()">
                <option value="">— Todos —</option>
                @foreach($horariosData as $h)
                <option value="{{ $h['id'] }}">{{ $h['nombre'] }}</option>
                @endforeach
            </select>

            <div class="vr mx-1"></div>

            {{-- Asignación masiva --}}
            <span class="text-muted small fw-semibold text-nowrap">
                <i class="fa-solid fa-users me-1"></i> Asignar a visibles:
            </span>
            <select id="selMasivo" class="form-select form-select-sm" style="max-width:250px">
                <option value="">— Selecciona un turno —</option>
                @foreach($horariosData as $h)
                <option value="{{ $h['id'] }}">{{ $h['nombre'] }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary" onclick="aplicarMasivo()">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Aplicar
            </button>

        </div>
    </div>
    @endcan

    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 w-100" id="turnoTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:42px"></th>
                        <th>Empleado</th>
                        <th>Cargo / Departamento</th>
                        <th>Turno actual</th>
                        <th>Turno semana siguiente</th>
                        <th style="width:90px" class="text-center">Estado</th>
                        @can('programacion_turno.eliminar')
                        <th style="width:50px"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody id="turnoTbody">
                    <tr id="trLoading">
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                            <p class="text-muted mt-2 mb-0 small">Cargando equipo...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="alertaGuardar" class="mt-3" style="display:none;"></div>
</div>
@endsection

@push('styles')
<style>
#turnoTable th, #turnoTable td { font-size: 13px; vertical-align: middle; }
.foto-mini {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; background: #e9ecef;
}
.foto-mini-placeholder {
    width: 36px; height: 36px; border-radius: 50%;
    background: #e9ecef; display: inline-flex;
    align-items: center; justify-content: center;
    font-size: 15px; color: #adb5bd;
}
</style>
@endpush

@push('scripts')
<script>
const csrfToken          = '{{ csrf_token() }}';
const semanaInicio       = '{{ $semanaInicio->toDateString() }}';
const canCrear           = {{ Auth::user()->can('programacion_turno.crear') ? 'true' : 'false' }};
const canEliminar        = {{ Auth::user()->can('programacion_turno.eliminar') ? 'true' : 'false' }};

const DIAS_CORTOS = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

const horarios = @json($horariosData);

let equipoData = [];

function getHorario(id) {
    return horarios.find(h => h.id === id) || null;
}

function buildResumenHorario(horarioId) {
    const h = getHorario(horarioId);
    if (!h) return '';

    const diasMap = {};
    (h.dias || []).forEach(d => { diasMap[d.dia] = d; });

    const celdas = DIAS_CORTOS.map((nombre, i) => {
        const d = diasMap[i + 1];
        if (d && d.hora_entrada) {
            return `<td class="text-center px-1" style="min-width:52px">
                        <span class="badge bg-primary-subtle text-primary fw-semibold d-block">${nombre}</span>
                        <small class="d-block" style="font-size:10px;line-height:1.3">${d.hora_entrada}</small>
                        <small class="d-block text-muted" style="font-size:10px;line-height:1.3">${d.hora_salida}</small>
                    </td>`;
        }
        return `<td class="text-center px-1" style="min-width:52px">
                    <span class="badge bg-light text-muted fw-normal d-block">${nombre}</span>
                    <small class="d-block text-muted" style="font-size:10px">—</small>
                </td>`;
    }).join('');

    return `<div class="mt-1 overflow-auto"><table class="mb-0" style="border-collapse:separate;border-spacing:2px 0"><tr>${celdas}</tr></table></div>`;
}

function buildSelect(userId, horarioProgramadoId, horarioActualId) {
    const valor = horarioProgramadoId ?? horarioActualId ?? '';

    if (!canCrear) {
        const h = getHorario(valor);
        return h
            ? `<span class="fw-semibold">${h.nombre}</span>${buildResumenHorario(h.id)}`
            : '<span class="text-muted">—</span>';
    }

    let opts = '<option value="">— Sin cambio —</option>';
    horarios.forEach(h => {
        const sel = h.id == valor ? 'selected' : '';
        opts += `<option value="${h.id}" ${sel}>${h.nombre}</option>`;
    });

    const resumenId = `resumen_sel_${userId}`;
    return `
        <select class="form-select form-select-sm" data-user="${userId}" id="sel_${userId}">${opts}</select>
        <div id="${resumenId}" class="mt-1">${buildResumenHorario(valor || null)}</div>
    `;
}

function buildFoto(foto_url, name) {
    if (foto_url) {
        return `<img src="${foto_url}" class="foto-mini" alt="${name}">`;
    }
    return `<span class="foto-mini-placeholder"><i class="fa-solid fa-user"></i></span>`;
}

function buildBadge(programadoId, actualId) {
    if (programadoId && programadoId !== actualId) {
        return '<span class="badge bg-warning text-dark">Cambio</span>';
    }
    if (programadoId && programadoId === actualId) {
        return '<span class="badge bg-secondary">Igual</span>';
    }
    return '<span class="badge bg-light text-muted border">Sin cambio</span>';
}

async function loadEquipo() {
    try {
        const res = await fetch('/admin/programacion-turno/data');
        const data = await res.json();
        equipoData = data.equipo || [];

        document.getElementById('trLoading')?.remove();
        const tbody = document.getElementById('turnoTbody');
        tbody.innerHTML = '';

        if (equipoData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-5">
                <i class="fa-solid fa-users-slash fa-2x mb-2 d-block"></i>
                No tienes empleados asignados a tu equipo.
            </td></tr>`;
            return;
        }

        equipoData.forEach(emp => {
            const tr = document.createElement('tr');
            tr.id = `row_${emp.id}`;
            tr.setAttribute('data-prog-id', emp.programacion_id ?? '');

            const elimBtn = canEliminar && emp.programacion_id
                ? `<td class="text-center"><button class="btn btn-sm btn-outline-danger p-1" onclick="eliminarProg(${emp.id}, ${emp.programacion_id})" title="Quitar programación"><i class="fa-solid fa-xmark"></i></button></td>`
                : (canEliminar ? `<td></td>` : '');

            tr.innerHTML = `
                <td class="text-center">${buildFoto(emp.foto_url, emp.name)}</td>
                <td><strong>${emp.name}</strong></td>
                <td><small class="text-muted">${emp.cargo} · ${emp.departamento}</small></td>
                <td>
                    <span class="badge bg-light text-dark border">${emp.horario_nombre}</span>
                    ${buildResumenHorario(emp.horario_id)}
                </td>
                <td>${buildSelect(emp.id, emp.horario_programado_id, emp.horario_id)}</td>
                <td class="text-center" id="badge_${emp.id}">${buildBadge(emp.horario_programado_id, emp.horario_id)}</td>
                ${elimBtn}
            `;
            tbody.appendChild(tr);
        });

        // Actualizar badge y resumen al cambiar el select
        document.querySelectorAll('#turnoTbody select').forEach(sel => {
            sel.addEventListener('change', function() {
                const uid = parseInt(this.dataset.user);
                const emp = equipoData.find(e => e.id === uid);
                const nuevoId = parseInt(this.value) || null;
                document.getElementById(`badge_${uid}`).innerHTML = buildBadge(nuevoId, emp?.horario_id);
                const resumenEl = document.getElementById(`resumen_sel_${uid}`);
                if (resumenEl) resumenEl.innerHTML = buildResumenHorario(nuevoId);
            });
        });

    } catch(e) {
        console.error(e);
        document.getElementById('turnoTbody').innerHTML =
            '<tr><td colspan="7" class="text-center text-danger py-4"><i class="fa-solid fa-triangle-exclamation me-1"></i>Error al cargar el equipo.</td></tr>';
    }
}

function filtrarPorTurno() {
    const filtroId = parseInt(document.getElementById('selFiltro').value) || null;

    document.querySelectorAll('#turnoTbody tr[id^="row_"]').forEach(tr => {
        const userId   = parseInt(tr.id.replace('row_', ''));
        const emp      = equipoData.find(e => e.id === userId);
        if (!emp) return;

        const visible = !filtroId || emp.horario_id === filtroId;
        tr.style.display = visible ? '' : 'none';
    });
}

function aplicarMasivo() {
    const horarioId = parseInt(document.getElementById('selMasivo').value);
    if (!horarioId) {
        mostrarAlerta('warning', 'Selecciona un turno para aplicar.');
        return;
    }

    // Solo aplica a filas visibles (respeta el filtro activo)
    let count = 0;
    document.querySelectorAll('#turnoTbody tr[id^="row_"]').forEach(tr => {
        if (tr.style.display === 'none') return;
        const sel = tr.querySelector('select');
        if (!sel) return;
        sel.value = horarioId;
        sel.dispatchEvent(new Event('change'));
        count++;
    });

    const nombre = horarios.find(h => h.id === horarioId)?.nombre ?? '';
    mostrarAlerta('success', `<i class="fa-solid fa-check me-1"></i>Turno <strong>${nombre}</strong> asignado a ${count} empleado(s) visible(s). Recuerda guardar.`);
}

async function guardarProgramacion() {
    const programaciones = [];

    document.querySelectorAll('#turnoTbody select').forEach(sel => {
        const userId   = parseInt(sel.dataset.user);
        const horarioId = parseInt(sel.value);
        if (userId && horarioId) {
            programaciones.push({ user_id: userId, horario_id: horarioId });
        }
    });

    if (programaciones.length === 0) {
        mostrarAlerta('warning', 'No hay cambios de turno seleccionados.');
        return;
    }

    try {
        const res = await fetch('/admin/programacion-turno', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ semana_inicio: semanaInicio, programaciones }),
        });
        const data = await res.json();
        if (res.ok) {
            mostrarAlerta('success', `<i class="fa-solid fa-check me-1"></i>${data.message}`);
            loadEquipo();
        } else {
            const msgs = Object.values(data.errors || {}).flat().join(' ') || data.message || 'Error';
            mostrarAlerta('danger', msgs);
        }
    } catch(e) {
        mostrarAlerta('danger', 'Error de conexión.');
        console.error(e);
    }
}

async function eliminarProg(userId, progId) {
    if (!confirm('¿Quitar la programación de este empleado?')) return;

    try {
        const res = await fetch(`/admin/programacion-turno/${progId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        if (res.ok) {
            loadEquipo();
        } else {
            const data = await res.json();
            mostrarAlerta('danger', data.message || 'Error al eliminar.');
        }
    } catch(e) {
        console.error(e);
    }
}

function mostrarAlerta(tipo, mensaje) {
    const el = document.getElementById('alertaGuardar');
    el.innerHTML = `<div class="alert alert-${tipo} py-2">${mensaje}</div>`;
    el.style.display = '';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    loadEquipo();
});
</script>
@endpush
