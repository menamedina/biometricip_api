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
                    <button class="btn btn-primary btn-sm me-2" onclick="abrirModalManual()">
                        <i class="fa-solid fa-plus me-1"></i> Registro Manual
                    </button>
                    <button class="btn btn-success btn-sm" onclick="exportar()">
                        <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Desde</label>
                    <input type="date" id="dateFrom" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Hasta</label>
                    <input type="date" id="dateTo" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Empleado</label>
                    <select id="filterEmpleado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Departamento</label>
                    <select id="filterDepto" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100" onclick="cargarResumen()">
                        <i class="fa-solid fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla resumen --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Empleado</th>
                        <th>Código</th>
                        <th>Departamento</th>
                        <th>Fecha</th>
                        <th>Entrada 1</th>
                        <th>Salida 1</th>
                        <th>Entrada 2</th>
                        <th>Salida 2</th>
                        <th>Entrada 3</th>
                        <th>Salida 3</th>
                        <th>Entrada 4</th>
                        <th>Salida 4</th>
                        <th class="text-end">Total Horas</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="resumenTbody">
                    <tr><td colspan="14" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted" id="resumenInfo"></small>
            <small class="text-muted" id="resumenTotal"></small>
        </div>
    </div>
</div>

{{-- Modal Editar Tipo --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-sm">
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
                    <label class="form-label">Fecha/Hora</label>
                    <input type="text" id="editFechaHora" class="form-control form-control-sm" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select id="editTipo" class="form-select form-select-sm">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
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
                    <label class="form-label">Empleado</label>
                    <select id="manualEmpleado" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
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
                        <label class="form-label">Fecha</label>
                        <input type="date" id="manualFecha" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hora</label>
                        <input type="time" id="manualHora" class="form-control form-control-sm" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="guardarManual()">Crear Registro</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
let deptoMap = {};
let allRegistros = []; // guardar todos los registros para acceder al editar

// ── Inicialización ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const hoy   = new Date().toISOString().slice(0, 10);
    const lunes = inicioSemana();
    document.getElementById('dateFrom').value = lunes;
    document.getElementById('dateTo').value   = hoy;

    cargarFiltros();
    cargarResumen();
});

function inicioSemana() {
    const d = new Date();
    const day = d.getDay() || 7;
    d.setDate(d.getDate() - day + 1);
    return d.toISOString().slice(0, 10);
}

async function cargarFiltros() {
    const [resE, resCat] = await Promise.all([
        fetch('/api/empleados?per_page=500', { headers: { 'Authorization': `Bearer ${token}` } }),
        fetch('/api/catalogos',              { headers: { 'Authorization': `Bearer ${token}` } }),
    ]);
    const dataE   = await resE.json();
    const dataCat = await resCat.json();

    const deptos = dataCat.departamentos || [];
    deptoMap = Object.fromEntries(deptos.map(d => [d.id, d.nombre]));

    const empleados = dataE.data || [];

    const selE = document.getElementById('filterEmpleado');
    const selManual = document.getElementById('manualEmpleado');
    empleados.forEach(e => {
        const opt = `<option value="${e.id}">${e.name} (${e.codigo_empleado || ''})</option>`;
        selE.innerHTML += opt;
        selManual.innerHTML += opt;
    });

    const selD = document.getElementById('filterDepto');
    deptos.forEach(d => {
        selD.innerHTML += `<option value="${d.id}">${d.nombre}</option>`;
    });
}

// ── Cargar resumen ────────────────────────────────────────────────────────────
async function cargarResumen() {
    const from    = document.getElementById('dateFrom').value;
    const to      = document.getElementById('dateTo').value;
    const userId  = document.getElementById('filterEmpleado').value;
    const deptoId = document.getElementById('filterDepto').value;

    if (!from || !to) { alert('Selecciona el rango de fechas.'); return; }

    let url = `/api/attendance?per_page=2000&date_from=${from}&date_to=${to}`;
    if (userId)  url += `&user_id=${userId}`;

    const tbody = document.getElementById('resumenTbody');
    tbody.innerHTML = '<tr><td colspan="14" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</td></tr>';

    try {
        const res  = await fetch(url, { headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || `HTTP ${res.status}`);
        }
        const data = await res.json();
        let registros = data.data || [];

        if (deptoId) {
            registros = registros.filter(r => r.user?.departamento_id == deptoId);
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
            if (!grupos[key]) grupos[key] = { user: r.user, fecha, registros: [] };
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

            const horario = sorted.find(r => r.horario)?.horario;
            if (horario?.duracion_almuerzo_min && totalMin > horario.duracion_almuerzo_min) {
                totalMin = totalMin - horario.duracion_almuerzo_min;
            }

            // Construir celdas clickeables (hasta 4 pares entrada/salida)
            const fmtHora = str => toDate(str).toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'America/Bogota'});
            const celdas = [];
            for (let i = 0; i < 4; i++) {
                const s = sessions[i];
                if (s?.e) {
                    celdas.push(`<span class="text-success fw-semibold cursor-pointer" onclick="editarRegistro(${s.e.id})" title="Click para editar">${fmtHora(s.e.fecha_hora)} <i class="fa-solid fa-pen fa-xs text-muted"></i></span>`);
                } else {
                    celdas.push('<span class="text-muted">—</span>');
                }
                if (s?.s) {
                    celdas.push(`<span class="text-danger fw-semibold cursor-pointer" onclick="editarRegistro(${s.s.id})" title="Click para editar">${fmtHora(s.s.fecha_hora)} <i class="fa-solid fa-pen fa-xs text-muted"></i></span>`);
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

            // Botón para agregar registro en ese día para ese usuario
            const btnAdd = `<button class="btn btn-outline-primary btn-sm py-0 px-1" onclick="abrirModalManualPre(${g.user?.id}, '${g.fecha}')" title="Agregar registro"><i class="fa-solid fa-plus fa-xs"></i></button>`;

            filas += `<tr>
                <td>${g.user?.name ?? 'N/A'}</td>
                <td><span class="badge bg-primary">${g.user?.codigo_empleado ?? '—'}</span></td>
                <td><small class="text-muted">${deptoNombre}</small></td>
                <td>${fechaFmt}</td>
                ${celdas.map(c => `<td>${c}</td>`).join('')}
                <td class="text-end">${totalStr}</td>
                <td class="text-center">${btnAdd}</td>
            </tr>`;
        });

        tbody.innerHTML = filas;

        const th = Math.floor(totalMinGlobal / 60);
        const tm = totalMinGlobal % 60;
        document.getElementById('resumenInfo').textContent  = `${totalDias} días · ${registros.length} marcaciones`;
        document.getElementById('resumenTotal').textContent = `Total período: ${th}h ${String(tm).padStart(2,'0')}m`;

    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="14" class="text-center text-danger py-3">Error al cargar datos: ${e.message}</td></tr>`;
        console.error('cargarResumen error:', e);
    }
}

// ── Editar registro (cambiar tipo) ───────────────────────────────────────────
function editarRegistro(id) {
    const reg = allRegistros.find(r => r.id === id);
    if (!reg) return;

    document.getElementById('editId').value = id;
    document.getElementById('editEmpleado').value = reg.user?.name || 'N/A';
    document.getElementById('editFechaHora').value = reg.fecha_hora;
    document.getElementById('editTipo').value = reg.tipo;

    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

async function guardarEdicion() {
    const id   = document.getElementById('editId').value;
    const tipo = document.getElementById('editTipo').value;

    try {
        const res = await fetch(`/api/attendance/${id}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ tipo }),
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

// ── Registro Manual ──────────────────────────────────────────────────────────
function abrirModalManual() {
    document.getElementById('manualEmpleado').value = '';
    document.getElementById('manualTipo').value = 'entrada';
    document.getElementById('manualFecha').value = new Date().toISOString().slice(0, 10);
    document.getElementById('manualHora').value = '';
    new bootstrap.Modal(document.getElementById('modalManual')).show();
}

function abrirModalManualPre(userId, fecha) {
    document.getElementById('manualEmpleado').value = userId;
    document.getElementById('manualTipo').value = 'entrada';
    document.getElementById('manualFecha').value = fecha;
    document.getElementById('manualHora').value = '';
    new bootstrap.Modal(document.getElementById('modalManual')).show();
}

async function guardarManual() {
    const userId = document.getElementById('manualEmpleado').value;
    const tipo   = document.getElementById('manualTipo').value;
    const fecha  = document.getElementById('manualFecha').value;
    const hora   = document.getElementById('manualHora').value;

    if (!userId || !fecha || !hora) {
        alert('Completa todos los campos.');
        return;
    }

    const fechaHora = `${fecha} ${hora}:00`;

    try {
        const res = await fetch('/api/attendance/manual', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ user_id: parseInt(userId), tipo, fecha_hora: fechaHora }),
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
