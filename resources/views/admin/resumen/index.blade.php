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
                <button class="btn btn-success" onclick="exportar()">
                    <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
                </button>
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

                    </tr>
                </thead>
                <tbody id="resumenTbody">
                    <tr><td colspan="13" class="text-center text-muted py-4">Selecciona un rango de fechas y presiona Buscar</td></tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted" id="resumenInfo"></small>
            <small class="text-muted" id="resumenTotal"></small>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
let deptoMap = {};  // id → nombre, para resolver en la tabla

// ── Inicialización ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const hoy   = new Date().toISOString().slice(0, 10);
    const lunes = inicioSemana();
    document.getElementById('dateFrom').value = lunes;
    document.getElementById('dateTo').value   = hoy;

    cargarFiltros();
});

function inicioSemana() {
    const d = new Date();
    const day = d.getDay() || 7;
    d.setDate(d.getDate() - day + 1);
    return d.toISOString().slice(0, 10);
}

async function cargarFiltros() {
    // Una sola llamada para empleados + catálogos en paralelo
    const [resE, resCat] = await Promise.all([
        fetch('/api/empleados?per_page=500', { headers: { 'Authorization': `Bearer ${token}` } }),
        fetch('/api/catalogos',              { headers: { 'Authorization': `Bearer ${token}` } }),
    ]);
    const dataE   = await resE.json();
    const dataCat = await resCat.json();

    const deptos = dataCat.departamentos || [];
    deptoMap = Object.fromEntries(deptos.map(d => [d.id, d.nombre]));

    const selE = document.getElementById('filterEmpleado');
    (dataE.data || []).forEach(e => {
        selE.innerHTML += `<option value="${e.id}">${e.name} (${e.codigo_empleado})</option>`;
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
    tbody.innerHTML = '<tr><td colspan="13" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</td></tr>';

    try {
        const res  = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        let registros = data.data || [];

        // Filtrar por departamento en el cliente si se seleccionó
        if (deptoId) {
            registros = registros.filter(r => r.user?.departamento_id == deptoId);
        }

        if (!registros.length) {
            tbody.innerHTML = '<tr><td colspan="13" class="text-center text-muted py-4">Sin registros para el período seleccionado</td></tr>';
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

            // Emparejar cronológicamente: entrada abre sesión, salida la cierra
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

            // Calcular minutos trabajados
            let totalMin = 0;
            for (const s of sessions) {
                if (s.e && s.s) {
                    totalMin += Math.round((new Date(s.s.fecha_hora) - new Date(s.e.fecha_hora)) / 60000);
                }
            }

            // Auto-descuento si el horario tiene duracion_almuerzo_min
            const horario = sorted.find(r => r.horario)?.horario;
            if (horario?.duracion_almuerzo_min) {
                totalMin = Math.max(0, totalMin - horario.duracion_almuerzo_min);
            }

            // Construir celdas (hasta 4 pares entrada/salida)
            const celdas = [];
            for (let i = 0; i < 4; i++) {
                const s = sessions[i];
                celdas.push(s?.e ? `<span class="text-success fw-semibold">${s.e.fecha_hora.slice(11,16)}</span>` : '<span class="text-muted">—</span>');
                celdas.push(s?.s ? `<span class="text-danger fw-semibold">${s.s.fecha_hora.slice(11,16)}</span>` : '<span class="text-muted">—</span>');
            }

            totalMinGlobal += totalMin;
            totalDias++;

            const totalStr = totalMin > 0
                ? `<strong>${Math.floor(totalMin/60)}h ${String(totalMin%60).padStart(2,'0')}m</strong>`
                : '<span class="text-muted">—</span>';

            const deptoNombre = deptoMap[g.user?.departamento_id] || g.user?.departamento || '—';
            const fechaFmt    = g.fecha.split('-').reverse().join('/');

            filas += `<tr>
                <td>${g.user?.name ?? 'N/A'}</td>
                <td><span class="badge bg-primary">${g.user?.codigo_empleado ?? '—'}</span></td>
                <td><small class="text-muted">${deptoNombre}</small></td>
                <td>${fechaFmt}</td>
                ${celdas.map(c => `<td>${c}</td>`).join('')}
                <td class="text-end">${totalStr}</td>
            </tr>`;
        });

        tbody.innerHTML = filas;

        const th = Math.floor(totalMinGlobal / 60);
        const tm = totalMinGlobal % 60;
        document.getElementById('resumenInfo').textContent  = `${totalDias} días · ${registros.length} marcaciones`;
        document.getElementById('resumenTotal').textContent = `Total período: ${th}h ${String(tm).padStart(2,'0')}m`;

    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="13" class="text-center text-danger py-3">Error al cargar datos</td></tr>`;
        console.error(e);
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
@endpush
