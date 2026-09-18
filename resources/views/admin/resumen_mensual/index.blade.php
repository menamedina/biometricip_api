@extends('layouts.admin')
@section('title', 'Resumen Mensual')

@section('content')
<div class="container-fluid">
    {{-- Encabezado --}}
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>Resumen Mensual</h4>
                    <p class="text-muted mb-0">Horas trabajadas por persona y día del mes</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleFullscreen()" title="Pantalla completa" id="btnFullscreen">
                        <i class="fa-solid fa-expand" id="iconFullscreen"></i>
                    </button>
                    @can('resumen_mensual.exportar')
                    <button class="btn btn-success btn-sm" onclick="exportarCSV()">
                        <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
                    </button>
                    @else
                    <button class="btn btn-success btn-sm" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;">
                        <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3 shadow-lg border-0">
        <div class="card-body p-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Mes</label>
                    <select id="filterMes" class="form-select form-select-sm">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Año</label>
                    <input type="number" id="filterAnio" class="form-control form-control-sm" min="2020" max="2100">
                </div>
                @cannot('empleados.ver')
                {{-- Solo se oculta este bloque para empleados --}}
                @else
                <div class="col-md-3" id="wrapFilterEmpleado">
                    <label class="form-label form-label-sm mb-1">Empleado</label>
                    <select id="filterEmpleado" class="form-select form-select-sm">
                        <option value="">Todos los empleados</option>
                    </select>
                </div>
                <div class="col-md-3" id="wrapFilterDepto">
                    <label class="form-label form-label-sm mb-1">Departamento</label>
                    <select id="filterDepto" class="form-select form-select-sm">
                        <option value="">Todos los departamentos</option>
                    </select>
                </div>
                @endcannot
                <div class="col-md-auto d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-sm" onclick="cargarMensual()">
                        <i class="fa-solid fa-search me-1"></i> Buscar
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="limpiarFiltros()">
                        <i class="fa-solid fa-xmark me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Totales resumen --}}
    <div id="resumenTotales" class="row g-3 mb-3" style="display:none!important;"></div>

    {{-- Tabla pivot --}}
    <div class="card shadow-lg border-0" id="tablaWrapper">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 table-bordered" id="mensualTable" style="font-size:12px;">
                    <thead class="table-light" id="mensualThead"></thead>
                    <tbody id="mensualTbody">
                        <tr>
                            <td colspan="35" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                                <p class="text-muted mt-2 mb-0 small">Cargando...</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="mensualTfoot"></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
div.dataTables_wrapper div.dataTables_length,
div.dataTables_wrapper div.dataTables_filter { padding: 10px 12px 0; }
div.dataTables_wrapper div.dataTables_info,
div.dataTables_wrapper div.dataTables_paginate { padding: 8px 12px 10px; border-top: 1px solid #e9ecef; }
div.dataTables_wrapper div.dataTables_length label,
div.dataTables_wrapper div.dataTables_filter label { font-size: 12px; color: #6c757d; margin-bottom: 6px; }
div.dataTables_wrapper div.dataTables_info { font-size: 12px; color: #6c757d; }
</style>
<style>
#mensualTable th, #mensualTable td {
    vertical-align: middle;
    white-space: nowrap;
}
#mensualTable th.dia-col {
    min-width: 52px;
    text-align: center;
}
#mensualTable td.dia-col {
    text-align: center;
    font-size: 11px;
}
#mensualTable td.celda-ok      { background-color: #e8f5e9 !important; color: #2e7d32 !important; font-weight: 600; }
#mensualTable td.celda-parcial { background-color: #fdecea !important; color: #c62828 !important; font-weight: 600; }
#mensualTable td.celda-ausente { background-color: #fafafa !important; color: #bdbdbd !important; }
#mensualTable td.celda-fin     { background-color: #f5f5f5 !important; color: #9e9e9e !important; font-style: italic; }

/* Pantalla completa */
#tablaWrapper:fullscreen,
#tablaWrapper:-webkit-full-screen,
#tablaWrapper:-moz-full-screen {
    background: #fff;
    padding: 16px;
    overflow: auto;
}
#tablaWrapper:fullscreen .table-responsive,
#tablaWrapper:-webkit-full-screen .table-responsive,
#tablaWrapper:-moz-full-screen .table-responsive {
    max-height: calc(100vh - 32px);
    overflow: auto;
}
#mensualTable tfoot td { font-weight: 700; background: #f8f9fa; font-size: 11px; }
.col-empleado-fijo { min-width: 160px; max-width: 200px; position: sticky; left: 0; background: #fff; z-index: 2; box-shadow: 2px 0 4px rgba(0,0,0,.05); }
.col-total-fijo    { min-width: 70px;  position: sticky; right: 0; background: #f8f9fa; z-index: 2; box-shadow: -2px 0 4px rgba(0,0,0,.05); font-weight: 700; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const csrfToken   = '{{ csrf_token() }}';
const esEmpleado  = {{ auth()->user()->cannot('empleados.ver') ? 'true' : 'false' }};
const myUserId    = {{ auth()->id() }};
let deptoMap      = {};
let _tablaData    = [];   // datos crudos para exportar

const DIAS_SEMANA = ['D','L','M','X','J','V','S'];
const MESES_ES    = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// ── Inicialización ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    const hoy = new Date();
    document.getElementById('filterMes').value  = hoy.getMonth() + 1;
    document.getElementById('filterAnio').value = hoy.getFullYear();

    await cargarCatalogos();
    cargarMensual();
});

async function cargarCatalogos() {
    try {
        const res  = await fetch('/admin/catalogos', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();

        const deptos = data.departamentos || [];
        deptoMap = Object.fromEntries(deptos.map(d => [d.id, d.nombre]));

        const selD = document.getElementById('filterDepto');
        if (selD) deptos.forEach(d => selD.innerHTML += `<option value="${d.id}">${d.nombre}</option>`);

        if (!esEmpleado) {
            const resE = await fetch('/admin/empleados/list?per_page=500&fields=id,name,codigo_empleado,departamento_id', {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const dataE = await resE.json();
            const empleados = dataE.data || [];
            const selE = document.getElementById('filterEmpleado');
            if (selE) empleados.forEach(e => {
                selE.innerHTML += `<option value="${e.id}">${e.name} (${e.codigo_empleado || ''})</option>`;
            });
        }
    } catch(e) { console.error('cargarCatalogos:', e); }
}

// ── Cargar tabla mensual ──────────────────────────────────────────────────────
async function cargarMensual() {
    const mes   = parseInt(document.getElementById('filterMes').value);
    const anio  = parseInt(document.getElementById('filterAnio').value);
    const deptoId   = document.getElementById('filterDepto')?.value   || '';
    const empleadoId = document.getElementById('filterEmpleado')?.value || '';

    const dias = diasEnMes(anio, mes);

    const thead  = document.getElementById('mensualThead');
    const tbody  = document.getElementById('mensualTbody');
    const tfoot  = document.getElementById('mensualTfoot');
    const totDiv = document.getElementById('resumenTotales');

    // Spinner
    if ($.fn.DataTable.isDataTable('#mensualTable')) {
        $('#mensualTable').DataTable().destroy();
    }
    thead.innerHTML = '';
    tfoot.innerHTML = '';
    totDiv.style.display = 'none';
    tbody.innerHTML = `<tr><td colspan="${dias + 3}" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
        <p class="text-muted mt-2 mb-0 small">Cargando datos...</p></td></tr>`;

    try {
        let url = `/admin/resumen-mensual/data?anio=${anio}&mes=${mes}`;
        if (!esEmpleado && empleadoId) url += `&user_id=${empleadoId}`;

        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        let datos  = json.data || [];

        // Filtro cliente: departamento
        if (!esEmpleado && deptoId) {
            datos = datos.filter(d => d.departamento_id == deptoId);
        }

        _tablaData = datos;

        if (!datos.length) {
            tbody.innerHTML = `<tr><td colspan="${dias + 3}" class="text-center text-muted py-4">Sin registros para el período seleccionado</td></tr>`;
            return;
        }

        // Agrupar por empleado
        const porEmpleado = {};
        datos.forEach(d => {
            if (!porEmpleado[d.user_id]) {
                porEmpleado[d.user_id] = {
                    nombre: d.nombre,
                    codigo: d.codigo_empleado,
                    dias: {}
                };
            }
            porEmpleado[d.user_id].dias[d.fecha] = {
                total_min:     d.total_min,
                min_esperados: d.min_esperados ?? 0,
            };
        });

        // ── Cabecera ──────────────────────────────────────────────────────────
        let thDias = '';
        const colTotalesDia = {};
        for (let d = 1; d <= dias; d++) {
            const fecha = `${anio}-${String(mes).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const dow   = new Date(anio, mes - 1, d).getDay(); // 0=dom
            const esFS  = dow === 0 || dow === 6;
            colTotalesDia[d] = 0;
            thDias += `<th class="dia-col ${esFS ? 'table-secondary' : ''}" title="${fecha}">
                <div class="fw-bold">${d}</div>
                <div style="font-size:9px;opacity:.7;">${DIAS_SEMANA[dow]}</div>
            </th>`;
        }

        thead.innerHTML = `<tr>
            <th class="col-empleado-fijo">Empleado</th>
            <th style="min-width:64px;">Código</th>
            ${thDias}
            <th class="col-total-fijo text-end">Total</th>
        </tr>`;

        // ── Filas ─────────────────────────────────────────────────────────────
        let filas          = '';
        const empleadosSorted = Object.values(porEmpleado).sort((a, b) => a.nombre.localeCompare(b.nombre));
        let totalGlobalMin = 0;

        empleadosSorted.forEach(emp => {
            let totalEmpleadoMin = 0;
            let celdas = '';

            for (let d = 1; d <= dias; d++) {
                const fecha = `${anio}-${String(mes).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const dow   = new Date(anio, mes - 1, d).getDay();
                const esFS  = dow === 0 || dow === 6;
                const diaData = emp.dias[fecha] ?? null;
            const mins    = diaData;

                if (esFS) {
                    celdas += `<td class="dia-col celda-fin">·</td>`;
                } else if (mins === null) {
                    celdas += `<td class="dia-col celda-ausente">—</td>`;
                } else {
                    const totalMin    = mins.total_min;
                    const minEsp      = mins.min_esperados;
                    const h = String(Math.floor(totalMin / 60)).padStart(2, '0');
                    const m = String(totalMin % 60).padStart(2, '0');
                    // Verde si cumplió el horario esperado (o no hay horario definido y trabajó algo)
                    const cumple = minEsp > 0 ? totalMin >= minEsp : totalMin >= 420;
                    const cls = cumple ? 'celda-ok' : 'celda-parcial';
                    const hEsp = String(Math.floor(minEsp/60)).padStart(2,'0');
                    const mEsp = String(minEsp%60).padStart(2,'0');
                    const tooltip = minEsp > 0
                        ? `${fecha}: ${h}:${m} trabajadas / ${hEsp}:${mEsp} esperadas`
                        : `${fecha}: ${h}:${m} trabajadas`;
                    celdas += `<td class="dia-col ${cls}" title="${tooltip}">${h}:${m}</td>`;
                    totalEmpleadoMin += totalMin;
                    colTotalesDia[d] = (colTotalesDia[d] || 0) + totalMin;
                }
            }

            totalGlobalMin += totalEmpleadoMin;
            const th = String(Math.floor(totalEmpleadoMin / 60)).padStart(2, '0');
            const tm = String(totalEmpleadoMin % 60).padStart(2, '0');

            filas += `<tr>
                <td class="col-empleado-fijo">
                    <div class="fw-semibold text-truncate" style="max-width:190px;" title="${emp.nombre}">${emp.nombre}</div>
                </td>
                <td><span class="badge bg-primary">${emp.codigo || '—'}</span></td>
                ${celdas}
                <td class="col-total-fijo text-end">${th}:${tm}</td>
            </tr>`;
        });

        tbody.innerHTML = filas;

        // ── DataTable ──────────────────────────────────────────────────────────
        // Columnas de días: índices 2 hasta (dias+1), sin ordenamiento
        const dayTargets = Array.from({ length: dias }, (_, i) => i + 2);
        $('#mensualTable').DataTable({
            paging:     true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100, 200],
            ordering:   true,
            scrollX:    true,
            searching:  true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
            },
            columnDefs: [
                { orderable: false, targets: dayTargets },
                { orderable: false, targets: -1 },       // Total
            ],
            order: [[0, 'asc']],
            initComplete: function () {
                $('#mensualTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                $('#mensualTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
            },
        });

        // ── Pie totales por día ───────────────────────────────────────────────
        let pieDias = '';
        for (let d = 1; d <= dias; d++) {
            const fecha = `${anio}-${String(mes).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const dow   = new Date(anio, mes - 1, d).getDay();
            const esFS  = dow === 0 || dow === 6;
            const mins  = colTotalesDia[d] || 0;
            if (esFS || mins === 0) {
                pieDias += `<td class="dia-col text-muted">—</td>`;
            } else {
                const h = String(Math.floor(mins / 60)).padStart(2, '0');
                const m = String(mins % 60).padStart(2, '0');
                pieDias += `<td class="dia-col">${h}:${m}</td>`;
            }
        }
        const tgh = String(Math.floor(totalGlobalMin / 60)).padStart(2, '0');
        const tgm = String(totalGlobalMin % 60).padStart(2, '0');
        tfoot.innerHTML = `<tr>
            <td class="col-empleado-fijo fw-bold">Total</td>
            <td></td>
            ${pieDias}
            <td class="col-total-fijo text-end">${tgh}:${tgm}</td>
        </tr>`;

        // ── KPI cards ─────────────────────────────────────────────────────────
        const totalEmpleados = empleadosSorted.length;
        const diasHabiles    = contarDiasHabiles(anio, mes);
        const promHoras      = totalEmpleados > 0
            ? (totalGlobalMin / totalEmpleados / 60).toFixed(1)
            : 0;

        totDiv.innerHTML = `
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-primary">${totalEmpleados}</div>
                    <div class="text-muted small">Empleados con registros</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-success">${tgh}:${tgm}</div>
                    <div class="text-muted small">Total horas del mes</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-warning">${promHoras}h</div>
                    <div class="text-muted small">Promedio por empleado</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-info">${diasHabiles}</div>
                    <div class="text-muted small">Días hábiles del mes</div>
                </div>
            </div>`;
        totDiv.style.removeProperty('display');
        totDiv.style.display = 'flex';
        // re-apply row class
        totDiv.className = 'row g-3 mb-3';

    } catch(e) {
        const dias2 = diasEnMes(parseInt(document.getElementById('filterAnio').value), parseInt(document.getElementById('filterMes').value));
        tbody.innerHTML = `<tr><td colspan="${dias2 + 3}" class="text-center text-danger py-3">Error al cargar datos: ${e.message}</td></tr>`;
        console.error('cargarMensual:', e);
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function diasEnMes(anio, mes) {
    return new Date(anio, mes, 0).getDate();
}

function contarDiasHabiles(anio, mes) {
    const total = diasEnMes(anio, mes);
    let habiles = 0;
    for (let d = 1; d <= total; d++) {
        const dow = new Date(anio, mes - 1, d).getDay();
        if (dow !== 0 && dow !== 6) habiles++;
    }
    return habiles;
}

// ── Limpiar filtros ───────────────────────────────────────────────────────────
function limpiarFiltros() {
    const hoy = new Date();
    document.getElementById('filterMes').value  = hoy.getMonth() + 1;
    document.getElementById('filterAnio').value = hoy.getFullYear();
    const selE = document.getElementById('filterEmpleado');
    const selD = document.getElementById('filterDepto');
    if (selE) selE.value = '';
    if (selD) selD.value = '';
    cargarMensual();
}

// ── Pantalla completa ─────────────────────────────────────────────────────────
function toggleFullscreen() {
    const el   = document.getElementById('tablaWrapper');
    const icon = document.getElementById('iconFullscreen');
    if (!document.fullscreenElement) {
        (el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen).call(el);
    } else {
        (document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen).call(document);
    }
}
document.addEventListener('fullscreenchange',       actualizarIconoFullscreen);
document.addEventListener('webkitfullscreenchange', actualizarIconoFullscreen);
document.addEventListener('mozfullscreenchange',    actualizarIconoFullscreen);
function actualizarIconoFullscreen() {
    const icon = document.getElementById('iconFullscreen');
    if (document.fullscreenElement) {
        icon.className = 'fa-solid fa-compress';
    } else {
        icon.className = 'fa-solid fa-expand';
    }
}

// ── Exportar CSV ──────────────────────────────────────────────────────────────
function exportarCSV() {
    if (!_tablaData.length) { alert('No hay datos para exportar.'); return; }

    const mes  = parseInt(document.getElementById('filterMes').value);
    const anio = parseInt(document.getElementById('filterAnio').value);
    const dias = diasEnMes(anio, mes);

    // Agrupar igual que la tabla
    const porEmpleado = {};
    _tablaData.forEach(d => {
        if (!porEmpleado[d.user_id]) {
            porEmpleado[d.user_id] = { nombre: d.nombre, codigo: d.codigo_empleado, dias: {} };
        }
        porEmpleado[d.user_id].dias[d.fecha] = d.total_min;
    });

    // Cabecera CSV
    let csvDias = '';
    for (let d = 1; d <= dias; d++) {
        csvDias += `,${d}`;
    }
    let csv = `Empleado,Código${csvDias},Total (h)\n`;

    Object.values(porEmpleado).sort((a, b) => a.nombre.localeCompare(b.nombre)).forEach(emp => {
        let totalMin = 0;
        let cols = '';
        for (let d = 1; d <= dias; d++) {
            const fecha    = `${anio}-${String(mes).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const diaData  = emp.dias[fecha] ?? null;
            const mins     = diaData ? diaData.total_min : 0;
            const h = Math.floor(mins / 60);
            const m = String(mins % 60).padStart(2, '0');
            cols += mins > 0 ? `,${h}:${m}` : ',';
            totalMin += mins;
        }
        const th = (totalMin / 60).toFixed(2);
        csv += `"${emp.nombre}","${emp.codigo || ''}"${cols},${th}\n`;
    });

    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `resumen_mensual_${MESES_ES[mes]}_${anio}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endpush
