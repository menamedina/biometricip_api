@extends('layouts.admin')
@section('title', 'Novedades de Nómina')

@section('content')
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>Novedades de Nómina</h4>
                    <p class="text-muted mb-0">Resumen de novedades por empleado para liquidación de nómina</p>
                </div>
                <div>
                    @can('novedades_nomina.exportar')
                    <button class="btn btn-success btn-sm disabled" id="btnExportarCSV" onclick="exportarCSV()" aria-disabled="true" tabindex="-1">
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
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Período</label>
                    <select id="filterPeriodo" class="form-select form-select-sm">
                        <option value="mensual">Mensual completo</option>
                        <option value="q1">Primera quincena (1–15)</option>
                        <option value="q2">Segunda quincena (16–fin)</option>
                    </select>
                </div>
                @cannot('empleados.ver')
                {{-- Empleado regular: sin filtros adicionales --}}
                @else
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Empleado</label>
                    <select id="filterEmpleado" class="form-select form-select-sm">
                        <option value="">Todos los empleados</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Departamento</label>
                    <select id="filterDepto" class="form-select form-select-sm">
                        <option value="">Todos los departamentos</option>
                    </select>
                </div>
                @endcannot
                <div class="col-md-auto d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-sm" onclick="cargarNovedades()">
                        <i class="fa-solid fa-search me-1"></i> Buscar
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="limpiarFiltros()">
                        <i class="fa-solid fa-xmark me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Aviso sin recargos --}}
    <div id="avisoSinRecargos" class="alert alert-info d-none py-2">
        <i class="fa-solid fa-circle-info me-1"></i>
        Las columnas <strong>H. Extra</strong>, <strong>Rec. Nocturno</strong> y <strong>Rec. Festivo</strong>
        muestran cero porque no se ha ejecutado el cálculo de Recargos para este período.
        Ve a <a href="{{ route('admin.recargos.index') }}" class="alert-link">Recargos</a> y calcula primero.
    </div>

    {{-- KPI Cards --}}
    <div id="kpiCards" class="row g-3 mb-3" style="display:none!important;"></div>

    {{-- Tabla --}}
    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:70vh;overflow:auto;">
                <table class="table table-hover table-sm mb-0 table-bordered" id="novedadesTable" style="font-size:12px;">
                    <thead class="table-light" id="novedadesThead">
                        <tr>
                            <th class="col-nom-empleado">Empleado</th>
                            <th class="col-nom-codigo">Código</th>
                            <th class="col-nom-depto">Departamento</th>
                            <th class="col-nom-habiles text-center">Días Háb.</th>
                            <th class="col-nom-ausencias text-center">Ausencias</th>
                            <th class="col-nom-tardanza text-center">Tardanza</th>
                            <th class="col-nom-extras text-center">H. Extra</th>
                            <th class="col-nom-nocturno text-center">Rec. Nocturno</th>
                            <th class="col-nom-festivo text-center">Rec. Festivo</th>
                            <th class="col-nom-permisos text-center">Permisos (días)</th>
                            <th class="col-nom-permisos-h text-center">Permisos (h)</th>
                        </tr>
                    </thead>
                    <tbody id="novedadesTbody">
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="fa-solid fa-filter fa-2x text-muted mb-2 d-block"></i>
                                <span class="text-muted small">Selecciona el período y haz clic en <strong>Buscar</strong></span>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
#filterEmpleado + .select2-container { width: 100% !important; }
#filterEmpleado + .select2-container .select2-selection--single { height: 31px; border-color: #dee2e6; border-radius: .25rem; }
#filterEmpleado + .select2-container .select2-selection__rendered { line-height: 29px; font-size: .875rem; }
#filterEmpleado + .select2-container .select2-selection__arrow { height: 29px; }
div.dataTables_wrapper div.dataTables_length,
div.dataTables_wrapper div.dataTables_filter { padding: 10px 12px 0; }
div.dataTables_wrapper div.dataTables_info,
div.dataTables_wrapper div.dataTables_paginate { padding: 8px 12px 10px; border-top: 1px solid #e9ecef; }
div.dataTables_wrapper div.dataTables_length label,
div.dataTables_wrapper div.dataTables_filter label { font-size: 12px; color: #6c757d; margin-bottom: 6px; }
div.dataTables_wrapper div.dataTables_info { font-size: 12px; color: #6c757d; }
#novedadesTable th, #novedadesTable td { vertical-align: middle; white-space: nowrap; font-size: 12px; }
.col-nom-empleado { min-width: 160px; position: sticky; left: 0; background: #fff !important; z-index: 3; box-shadow: 2px 0 4px rgba(0,0,0,.05); }
#novedadesTable thead th { position: sticky; top: 0; background: #fff !important; z-index: 2; }
#novedadesTable thead th.col-nom-empleado { z-index: 5; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const csrfToken  = '{{ csrf_token() }}';
const esEmpleado = {{ auth()->user()->cannot('empleados.ver') ? 'true' : 'false' }};
const isLider    = {{ \App\Models\User::where('lider_id', auth()->id())->exists() ? 'true' : 'false' }};
const myUserId   = {{ auth()->id() }};
const MESES_ES   = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

let _tablaData = [];
let _cargando  = false;

// ── Helpers de formato ────────────────────────────────────────────────────────
function fmtMin(min) {
    if (!min) return '—';
    const h = Math.floor(min / 60);
    const m = String(min % 60).padStart(2, '0');
    return `${h}h ${m}m`;
}

function fmtMinCorto(min) {
    if (!min) return '—';
    const h = Math.floor(min / 60);
    const m = String(min % 60).padStart(2, '0');
    return `${h}:${m}`;
}

// ── Inicialización ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    const hoy = new Date();
    document.getElementById('filterMes').value  = hoy.getMonth() + 1;
    document.getElementById('filterAnio').value = hoy.getFullYear();

    if (!esEmpleado) {
        const selE = document.getElementById('filterEmpleado');
        if (selE) {
            $(selE).select2({
                placeholder: 'Todos los empleados',
                allowClear: true,
                width: '100%',
                language: { noResults: () => 'No se encontraron empleados' }
            });
        }
        await cargarCatalogos();
    }
});

async function cargarCatalogos() {
    try {
        const res  = await fetch('/admin/catalogos', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();

        const selD = document.getElementById('filterDepto');
        if (selD) {
            (data.departamentos || []).forEach(d => {
                selD.innerHTML += `<option value="${d.id}">${d.nombre}</option>`;
            });
        }

        const selE = document.getElementById('filterEmpleado');
        if (selE) {
            let empUrl = '/admin/empleados/list?per_page=500&fields=id,name,codigo_empleado';
            if (isLider) empUrl += `&lider_id=${myUserId}`;
            const resE  = await fetch(empUrl, { headers: { 'X-CSRF-TOKEN': csrfToken } });
            const dataE = await resE.json();
            (dataE.data || []).forEach(e => {
                selE.innerHTML += `<option value="${e.id}">${e.name} (${e.codigo_empleado || ''})</option>`;
            });
        }
    } catch(e) { console.error('cargarCatalogos:', e); }
}

// ── Cargar novedades ──────────────────────────────────────────────────────────
async function cargarNovedades() {
    if (_cargando) return;
    _cargando = true;

    const mes      = document.getElementById('filterMes').value;
    const anio     = document.getElementById('filterAnio').value;
    const periodo  = document.getElementById('filterPeriodo').value;
    const empId    = document.getElementById('filterEmpleado')?.value || '';
    const deptoId  = document.getElementById('filterDepto')?.value   || '';

    const tbody = document.getElementById('novedadesTbody');
    const kpi   = document.getElementById('kpiCards');

    if ($.fn.DataTable.isDataTable('#novedadesTable')) {
        $('#novedadesTable').DataTable().destroy();
    }

    tbody.innerHTML = `<tr><td colspan="11" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
        <p class="text-muted mt-2 mb-0 small">Calculando novedades...</p>
    </td></tr>`;
    kpi.style.display = 'none';
    document.getElementById('avisoSinRecargos').classList.add('d-none');
    habilitarExportar(false);

    try {
        let url = `/admin/novedades-nomina/data?anio=${anio}&mes=${mes}&periodo=${periodo}`;
        if (empId)   url += `&user_id=${empId}`;
        if (deptoId) url += `&departamento_id=${deptoId}`;

        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();

        _tablaData = json.data || [];

        if (json.sin_recargos) {
            document.getElementById('avisoSinRecargos').classList.remove('d-none');
        }

        if (!_tablaData.length) {
            tbody.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-4">
                <i class="fa-solid fa-magnifying-glass fa-2x mb-2 d-block"></i>
                Sin novedades para el período seleccionado
            </td></tr>`;
            return;
        }

        // ── Renderizar filas ──────────────────────────────────────────────────
        let filas = '';
        _tablaData.forEach(e => {
            const badgeAus = e.ausencias > 0
                ? `<span class="badge bg-danger">${e.ausencias}</span>`
                : `<span class="text-muted">0</span>`;

            const badgeTard = e.tardanza_min > 0
                ? `<span class="badge bg-warning text-dark">${fmtMin(e.tardanza_min)}</span>`
                : `<span class="text-muted">—</span>`;

            const badgeExt = e.extras_min > 0
                ? `<span class="badge bg-success">${fmtMinCorto(e.extras_min)}</span>`
                : `<span class="text-muted">—</span>`;

            const noct  = e.recargo_nocturno_min > 0 ? fmtMinCorto(e.recargo_nocturno_min) : '—';
            const fest  = e.recargo_festivo_min  > 0 ? fmtMinCorto(e.recargo_festivo_min)  : '—';
            const perD  = e.permisos_dias  > 0 ? e.permisos_dias : '—';
            const perH  = e.permisos_min   > 0 ? fmtMinCorto(e.permisos_min) : '—';

            filas += `<tr>
                <td class="col-nom-empleado">
                    <div class="fw-semibold text-truncate" style="max-width:190px;" title="${e.nombre}">${e.nombre}</div>
                    <small class="text-muted">C.C. ${e.cedula}</small>
                </td>
                <td><span class="badge bg-primary">${e.codigo_empleado}</span></td>
                <td><small class="text-muted">${e.departamento}</small></td>
                <td class="text-center">${e.dias_habiles}</td>
                <td class="text-center">${badgeAus}</td>
                <td class="text-center">${badgeTard}</td>
                <td class="text-center">${badgeExt}</td>
                <td class="text-center">${noct}</td>
                <td class="text-center">${fest}</td>
                <td class="text-center">${perD}</td>
                <td class="text-center">${perH}</td>
            </tr>`;
        });

        tbody.innerHTML = filas;

        if ($.fn.DataTable.isDataTable('#novedadesTable')) {
            $('#novedadesTable').DataTable().destroy();
        }
        $('#novedadesTable').DataTable({
            paging:     true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100, 200],
            ordering:   true,
            searching:  true,
            scrollX:    false,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
            columnDefs: [{ orderable: false, targets: [] }],
            order: [[0, 'asc']],
            initComplete: function() {
                $('#novedadesTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                $('#novedadesTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
            },
        });

        // ── KPI Cards ─────────────────────────────────────────────────────────
        const totalEmp       = _tablaData.length;
        const totalAusencias = _tablaData.reduce((s, e) => s + e.ausencias, 0);
        const totalTardMin   = _tablaData.reduce((s, e) => s + e.tardanza_min, 0);
        const totalExtMin    = _tablaData.reduce((s, e) => s + e.extras_min, 0);

        kpi.innerHTML = `
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-primary">${totalEmp}</div>
                    <div class="text-muted small">Empleados</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-danger">${totalAusencias}</div>
                    <div class="text-muted small">Total ausencias</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-warning">${fmtMinCorto(totalTardMin)}</div>
                    <div class="text-muted small">Total tardanzas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fs-2 fw-bold text-success">${fmtMinCorto(totalExtMin)}</div>
                    <div class="text-muted small">Total horas extra</div>
                </div>
            </div>`;
        kpi.style.removeProperty('display');
        kpi.style.display = 'flex';
        kpi.className = 'row g-3 mb-3';

        habilitarExportar(true);

    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="11" class="text-center text-danger py-3">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>Error al cargar novedades: ${e.message}
        </td></tr>`;
        console.error('cargarNovedades:', e);
    } finally {
        _cargando = false;
    }
}

function habilitarExportar(habilitar) {
    const btn = document.getElementById('btnExportarCSV');
    if (!btn) return;
    if (habilitar) {
        btn.classList.remove('disabled');
        btn.removeAttribute('aria-disabled');
        btn.removeAttribute('tabindex');
    } else {
        btn.classList.add('disabled');
        btn.setAttribute('aria-disabled', 'true');
        btn.setAttribute('tabindex', '-1');
    }
}

// ── Exportar CSV ──────────────────────────────────────────────────────────────
function exportarCSV() {
    if (!_tablaData.length) { alert('No hay datos para exportar.'); return; }

    const mes     = parseInt(document.getElementById('filterMes').value);
    const anio    = document.getElementById('filterAnio').value;
    const periodo = document.getElementById('filterPeriodo').value;
    const periodoLabel = { mensual: 'mensual', q1: 'q1', q2: 'q2' }[periodo];

    const headers = [
        'Empleado','Cédula','Código','Departamento',
        'Días Hábiles','Ausencias',
        'Tardanza (min)','H. Extra (min)',
        'Rec. Nocturno (min)','Rec. Festivo (min)',
        'Permisos (días)','Permisos (min)'
    ];

    let csv = headers.join(',') + '\n';
    _tablaData.forEach(e => {
        const row = [
            `"${e.nombre}"`,
            e.cedula,
            e.codigo_empleado,
            `"${e.departamento}"`,
            e.dias_habiles,
            e.ausencias,
            e.tardanza_min,
            e.extras_min,
            e.recargo_nocturno_min,
            e.recargo_festivo_min,
            e.permisos_dias,
            e.permisos_min,
        ];
        csv += row.join(',') + '\n';
    });

    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `novedades_nomina_${MESES_ES[mes]}_${anio}_${periodoLabel}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

// ── Limpiar filtros ───────────────────────────────────────────────────────────
function limpiarFiltros() {
    const hoy = new Date();
    document.getElementById('filterMes').value     = hoy.getMonth() + 1;
    document.getElementById('filterAnio').value    = hoy.getFullYear();
    document.getElementById('filterPeriodo').value = 'mensual';
    const selE = document.getElementById('filterEmpleado');
    const selD = document.getElementById('filterDepto');
    if (selE) { $(selE).val(null).trigger('change'); }
    if (selD) selD.value = '';
    document.getElementById('avisoSinRecargos').classList.add('d-none');
}
</script>
@endpush
