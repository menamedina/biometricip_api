@extends('layouts.admin')
@section('title', 'Reporte de Recargos')

@section('content')
<div class="container-fluid">
    {{-- Encabezado --}}
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-receipt-2 me-2 text-primary"></i>Reporte de Recargos</h4>
                    <p class="text-muted mb-0">Desglose de recargos laborales por empleado (normativa colombiana CST)</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="exportarCSV()" id="btnExport" disabled>
                        <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
                    </button>
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
                    <label class="form-label form-label-sm mb-1">Ano</label>
                    <input type="number" id="filterAnio" class="form-control form-control-sm" min="2020" max="2100">
                </div>
                @cannot('empleados.ver')
                @else
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Empleado</label>
                    <select id="filterEmpleado" class="form-select form-select-sm">
                        <option value="">Todos los empleados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Departamento</label>
                    <select id="filterDepto" class="form-select form-select-sm">
                        <option value="">Todos los departamentos</option>
                    </select>
                </div>
                @endcannot
                <div class="col-md-auto d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-sm" onclick="calcularRecargos()">
                        <i class="fa-solid fa-calculator me-1"></i> Calcular
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="limpiarFiltros()">
                        <i class="fa-solid fa-xmark me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div id="resumenTotales" class="row g-3 mb-3" style="display:none!important;"></div>

    {{-- Tabla --}}
    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:70vh; overflow:auto;">
                <table class="table table-hover table-sm mb-0 table-bordered" id="recargosTable" style="font-size:12px;">
                    <thead class="table-light" id="recargosThead"></thead>
                    <tbody id="recargosTbody">
                        <tr>
                            <td colspan="15" class="text-center text-muted py-5">
                                <i class="ti ti-receipt-2" style="font-size:48px;opacity:.3;"></i>
                                <p class="mt-2 mb-0 small">Seleccione un periodo y haga clic en <strong>Calcular</strong></p>
                            </td>
                        </tr>
                    </tbody>
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

#recargosTable th, #recargosTable td { vertical-align: middle; white-space: nowrap; }
#recargosTable td.concepto-col { text-align: center; font-size: 11px; }
#recargosTable td.celda-con    { background-color: #e8f5e9 !important; color: #2e7d32 !important; font-weight: 600; }
#recargosTable td.celda-sin    { background-color: #fafafa !important; color: #bdbdbd !important; }

.col-empleado-fijo { min-width: 160px; max-width: 200px; position: sticky; left: 0; background: #fff !important; z-index: 3; box-shadow: 2px 0 4px rgba(0,0,0,.05); }
.col-total-fijo    { min-width: 80px;  position: sticky; right: 0; background: #f8f9fa !important; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,.05); font-weight: 700; }
#recargosTable thead th { position: sticky; top: 0; background: #fff !important; z-index: 2; }
#recargosTable thead th.col-empleado-fijo,
#recargosTable thead th.col-total-fijo { z-index: 5; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const csrfToken  = '{{ csrf_token() }}';
const esEmpleado = {{ auth()->user()->cannot('empleados.ver') ? 'true' : 'false' }};
let _tablaData   = [];
let _conceptos   = [];

const MESES_ES = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

document.addEventListener('DOMContentLoaded', async () => {
    const hoy = new Date();
    document.getElementById('filterMes').value  = hoy.getMonth() + 1;
    document.getElementById('filterAnio').value = hoy.getFullYear();
    await cargarCatalogos();
});

async function cargarCatalogos() {
    try {
        const res  = await fetch('/admin/catalogos', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();

        const deptos = data.departamentos || [];
        const selD = document.getElementById('filterDepto');
        if (selD) deptos.forEach(d => selD.innerHTML += `<option value="${d.id}">${d.nombre}</option>`);

        if (!esEmpleado) {
            const resE = await fetch('/admin/empleados/list?per_page=500&fields=id,name,codigo_empleado', {
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

function formatMinutos(min) {
    if (!min || min <= 0) return '';
    const h = String(Math.floor(min / 60)).padStart(2, '0');
    const m = String(Math.round(min % 60)).padStart(2, '0');
    return `${h}:${m}`;
}

async function calcularRecargos() {
    const mes  = parseInt(document.getElementById('filterMes').value);
    const anio = parseInt(document.getElementById('filterAnio').value);
    const empleadoId = document.getElementById('filterEmpleado')?.value || '';
    const deptoId    = document.getElementById('filterDepto')?.value || '';

    const thead  = document.getElementById('recargosThead');
    const tbody  = document.getElementById('recargosTbody');
    const totDiv = document.getElementById('resumenTotales');

    // Spinner
    if ($.fn.DataTable.isDataTable('#recargosTable')) {
        $('#recargosTable').DataTable().destroy();
    }
    thead.innerHTML = '';
    totDiv.style.display = 'none';
    tbody.innerHTML = `<tr><td colspan="15" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
        <p class="text-muted mt-2 mb-0 small">Calculando recargos...</p></td></tr>`;

    try {
        let url = `/admin/recargos/data?anio=${anio}&mes=${mes}`;
        if (!esEmpleado && empleadoId) url += `&user_id=${empleadoId}`;
        if (!esEmpleado && deptoId)    url += `&departamento_id=${deptoId}`;

        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();

        _tablaData = json.data || [];
        _conceptos = json.conceptos || [];

        if (!_tablaData.length) {
            tbody.innerHTML = `<tr><td colspan="${_conceptos.length + 4}" class="text-center text-muted py-4">Sin recargos para el periodo seleccionado</td></tr>`;
            document.getElementById('btnExport').disabled = true;
            return;
        }

        document.getElementById('btnExport').disabled = false;

        // Cabecera
        let thConceptos = '';
        _conceptos.forEach(c => {
            thConceptos += `<th class="text-center" style="min-width:90px;" title="${c.nombre} (${c.porcentaje}%)">
                <div class="fw-bold" style="font-size:10px;">${c.nombre}</div>
                <div style="font-size:9px;opacity:.7;">${c.porcentaje}%</div>
            </th>`;
        });

        thead.innerHTML = `<tr>
            <th class="col-empleado-fijo">Empleado</th>
            <th style="min-width:64px;">Codigo</th>
            ${thConceptos}
            <th class="col-total-fijo text-end">Total Recargos</th>
        </tr>`;

        // Filas
        let filas = '';
        const sorted = _tablaData.sort((a, b) => a.nombre.localeCompare(b.nombre));
        let totalGlobalMin = 0;

        sorted.forEach(emp => {
            let celdas = '';
            _conceptos.forEach(c => {
                const mins = emp.conceptos[c.codigo] || 0;
                if (mins > 0) {
                    celdas += `<td class="concepto-col celda-con" title="${c.nombre}: ${formatMinutos(mins)}">${formatMinutos(mins)}</td>`;
                } else {
                    celdas += `<td class="concepto-col celda-sin">-</td>`;
                }
            });

            totalGlobalMin += emp.total_recargo_min || 0;
            const totalFmt = formatMinutos(emp.total_recargo_min);

            filas += `<tr>
                <td class="col-empleado-fijo">
                    <div class="fw-semibold text-truncate" style="max-width:190px;" title="${emp.nombre}">${emp.nombre}</div>
                </td>
                <td><span class="badge bg-primary">${emp.codigo_empleado || '-'}</span></td>
                ${celdas}
                <td class="col-total-fijo text-end">${totalFmt || '-'}</td>
            </tr>`;
        });

        tbody.innerHTML = filas;

        // DataTable
        const conceptTargets = Array.from({ length: _conceptos.length }, (_, i) => i + 2);
        $('#recargosTable').DataTable({
            paging: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            ordering: true,
            searching: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
            },
            columnDefs: [
                { orderable: false, targets: conceptTargets },
                { orderable: false, targets: -1 },
            ],
            order: [[0, 'asc']],
            initComplete: function () {
                $('#recargosTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                $('#recargosTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
            },
        });

        // KPIs
        const totalEmpleados   = sorted.length;
        const totalHorasRecStr = formatMinutos(totalGlobalMin);
        const promRecargos     = totalEmpleados > 0 ? (totalGlobalMin / totalEmpleados / 60).toFixed(1) : 0;
        const conceptosActivos = _conceptos.length;

        totDiv.innerHTML = `
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-primary">${totalEmpleados}</div>
                    <div class="text-muted small">Empleados con recargos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-success">${totalHorasRecStr || '00:00'}</div>
                    <div class="text-muted small">Total horas recargo</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-warning">${promRecargos}h</div>
                    <div class="text-muted small">Promedio por empleado</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-info">${conceptosActivos}</div>
                    <div class="text-muted small">Conceptos activos</div>
                </div>
            </div>`;
        totDiv.style.removeProperty('display');
        totDiv.style.display = 'flex';
        totDiv.className = 'row g-3 mb-3';

    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="15" class="text-center text-danger py-3">Error al calcular recargos: ${e.message}</td></tr>`;
        console.error('calcularRecargos:', e);
    }
}

function limpiarFiltros() {
    const hoy = new Date();
    document.getElementById('filterMes').value  = hoy.getMonth() + 1;
    document.getElementById('filterAnio').value = hoy.getFullYear();
    const selE = document.getElementById('filterEmpleado');
    const selD = document.getElementById('filterDepto');
    if (selE) selE.value = '';
    if (selD) selD.value = '';
}

function exportarCSV() {
    if (!_tablaData.length || !_conceptos.length) { alert('No hay datos para exportar.'); return; }

    const mes  = parseInt(document.getElementById('filterMes').value);
    const anio = parseInt(document.getElementById('filterAnio').value);

    // Cabecera
    let csvConceptos = '';
    _conceptos.forEach(c => { csvConceptos += `,"${c.nombre} (${c.porcentaje}%)"`; });
    let csv = `Empleado,Codigo${csvConceptos},Total Recargos (h)\n`;

    const sorted = _tablaData.sort((a, b) => a.nombre.localeCompare(b.nombre));
    sorted.forEach(emp => {
        let cols = '';
        _conceptos.forEach(c => {
            const mins = emp.conceptos[c.codigo] || 0;
            cols += mins > 0 ? `,${formatMinutos(mins)}` : ',';
        });
        const totalH = emp.total_recargo_min > 0 ? (emp.total_recargo_min / 60).toFixed(2) : '';
        csv += `"${emp.nombre}","${emp.codigo_empleado || ''}"${cols},${totalH}\n`;
    });

    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `recargos_${MESES_ES[mes]}_${anio}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endpush
