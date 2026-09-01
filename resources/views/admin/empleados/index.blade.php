@extends('layouts.admin')
@section('title', 'Empleados')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-users me-2 text-primary"></i>Empleados</h4>
                    <p class="text-muted mb-0">Gestión del personal</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportarEmpleados()" @if(!auth()->user()->admin_tenant && !auth()->user()->exportar_empleados) disabled @endif>
                        <i class="fa-solid fa-file-export me-1"></i> Exportar
                    </button>
                    <button class="btn btn-outline-secondary" onclick="openImportEmpleados()" @if(!auth()->user()->admin_tenant && !auth()->user()->importar_empleados) disabled @endif>
                        <i class="fa-solid fa-file-import me-1"></i> Importar
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empleadoModal" onclick="resetForm()" @if(!auth()->user()->admin_tenant && !auth()->user()->crear_empleado) disabled @endif>
                        <i class="fa-solid fa-plus me-1"></i> Nuevo Empleado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-2">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Buscar..." oninput="loadEmpleados()">
                        </div>
                        @if(auth()->user()->admin_tenant)
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="filterEmpresa" onchange="loadEmpleados()">
                                <option value="">Todas las empresas</option>
                            </select>
                        </div>
                        @endif
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="filterDepto" onchange="loadEmpleados()">
                                <option value="">Todos los deptos.</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="filterSede" onchange="loadEmpleados()">
                                <option value="">Todas las sedes</option>
                            </select>
                        </div>
                        <div class="col-md-auto ms-auto d-flex align-items-center gap-2">
                            <label class="form-label mb-0 text-muted small text-nowrap">Mostrar</label>
                            <select class="form-select form-select-sm" id="perPageSelect" style="width:75px" onchange="loadEmpleados()">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-muted small text-nowrap">registros</span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-col="codigo_empleado">Código</th>
                                <th class="sortable" data-col="name">Nombre</th>
                                <th class="sortable" data-col="cedula">Cédula</th>
                                <th class="sortable" data-col="email">Email</th>
                                @if(auth()->user()->admin_tenant)
                                <th>Empresa</th>
                                @endif
                                <th class="sortable" data-col="role">Rol</th>
                                <th>Cargo / Departamento</th>
                                <th>Sede</th>
                                <th class="sortable" data-col="is_active">Estado</th>
                                <th>Rostros</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="empleadosTbody">
                            <tr><td colspan="9" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="empleadosInfo"></small>
                    <div id="empleadosPagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="empleadoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="empleadoModalTitle">Nuevo Empleado</h5>
                <span id="conteoUsuariosBadge" class="badge bg-info ms-2 d-none"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="empleadoModalLoader" class="d-none text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted mt-2 small">Cargando datos...</div>
                </div>
                <form id="empleadoForm">
                    <input type="hidden" id="empleadoId">
                    <div class="row">
                        @if(auth()->user()->admin_tenant)
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Empresa <span class="text-danger">*</span></label>
                            <select id="empEmpresaId" class="form-select" required>
                                <option value="">— Seleccionar empresa —</option>
                            </select>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cédula <span class="text-danger">*</span></label>
                            <input type="text" id="empCedula" class="form-control" required placeholder="Ej: 1234567890" inputmode="numeric">
                        </div>
                        <div class="col-md-6 mb-3" id="empCodigoRow" style="display:none">
                            <label class="form-label">Código Empleado</label>
                            <input type="text" id="empCodigo" class="form-control" readonly style="background-color:#f8f9fa;font-weight:600;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" id="empName" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="empEmail" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña <small class="text-muted">(dejar vacío para mantener)</small></label>
                            <input type="password" id="empPassword" class="form-control" minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" id="empTelefono" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departamento</label>
                            <select id="empDepartamento" class="form-select">
                                <option value="">— Sin departamento —</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cargo</label>
                            <select id="empCargo" class="form-select">
                                <option value="">— Sin cargo —</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Horario <span class="text-danger">*</span></label>
                            <select id="empHorario" class="form-select" required>
                                <option value="">— Seleccionar horario —</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Empleador</label>
                            <select id="empEmpleador" class="form-select">
                                <option value="">— Sin empleador —</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Líder</label>
                            <select id="empLider" class="form-select">
                                <option value="">— Sin líder —</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Centro de costo</label>
                            <input type="text" id="empCentroCosto" class="form-control" placeholder="Ej: CC-001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ruta</label>
                            <input type="text" id="empRuta" class="form-control" placeholder="Ej: Ruta Norte">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Sedes asignadas</label>
                            <div id="empSedesContainer" class="border rounded p-2" style="min-height:42px; max-height:160px; overflow-y:auto; background:#fff;">
                                <span class="text-muted small" id="empSedesVacio">Sin sedes disponibles</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Rol</label>
                            <select id="empRole" class="form-select" @if(!auth()->user()->isAdmin()) disabled @endif>
                                <option value="empleado">Empleado</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empActivo" checked>
                                <label class="form-check-label">Activo</label>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="empTratamientoInfo">
                            <div id="empTratamientoAlert" class="alert d-flex align-items-start gap-2 py-2 px-3 mb-3" role="alert">
                                <span id="empTratamientoIcon" class="fs-5"></span>
                                <div>
                                    <div class="fw-semibold small" id="empTratamientoTitulo"></div>
                                    <div class="small text-muted" id="empTratamientoDetalle"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-0 mb-2">
                    <div class="row">
                        <div class="col-12 mb-3 d-flex align-items-center gap-4">
                            <small class="text-muted fw-bold">Permisos empleados:</small>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empExportarEmpleados">
                                <label class="form-check-label">Exportar</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empImportarEmpleados">
                                <label class="form-check-label">Importar</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empCrearEmpleado">
                                <label class="form-check-label">Crear</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empEditarEmpleado">
                                <label class="form-check-label">Editar</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarEmpleado" class="btn btn-primary" onclick="saveEmpleado()">Guardar</button>
            </div>
        </div>
    </div>
</div>
{{-- Modal Import Empleados --}}
<div class="modal fade" id="importEmpleadosModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-file-import me-2"></i>Importar Empleados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    El archivo debe seguir la plantilla. Si el <strong>email ya existe</strong> el empleado se <strong>actualiza</strong>; si no existe se <strong>crea</strong>.<br>
                    La hoja <em>Listas</em> contiene los valores válidos para los campos con desplegable.
                </p>
                @if(auth()->user()->admin_tenant)
                <div class="mb-3">
                    <label class="form-label">Empresa <span class="text-danger">*</span></label>
                    <select id="importEmpresaId" class="form-select form-select-sm">
                        <option value="">— Seleccionar empresa —</option>
                        @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="mb-3">
                    <a id="importEmpleadosTemplateLink" href="{{ route('admin.empleados.template') }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-download me-1"></i> Descargar plantilla Excel
                    </a>
                </div>
                <div class="mb-0">
                    <label class="form-label">Archivo Excel <span class="text-danger">*</span></label>
                    <input type="file" id="importEmpleadosFile" class="form-control" accept=".xlsx,.xls,.csv">
                </div>
                <div id="importEmpleadosResult" class="mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="importEmpleadosCancelBtn" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="importEmpleadosSubmitBtn" class="btn btn-primary" onclick="ejecutarImportEmpleados()">
                    <i class="fa-solid fa-file-import me-1"></i> Importar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Rostros --}}
<div class="modal fade" id="rostrosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-face-smile me-2 text-primary"></i>Rostros registrados — <span id="rostrosNombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="rostrosContent" class="text-center text-muted py-3">Cargando...</div>
            </div>
        </div>
    </div>
</div>
{{-- Modal Log Empleado --}}
<div class="modal fade" id="modalLogEmpleado" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de cambios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="logEmpleadoSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="logEmpleadoContent" class="d-none">
                    <div class="card border mb-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:130px">Fecha</th>
                                            <th style="width:80px">Evento</th>
                                            <th style="width:140px">Usuario</th>
                                            <th>Campo</th>
                                            <th>Anterior</th>
                                            <th>Nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logEmpleadoTbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="logEmpleadoVacio" class="text-center text-muted py-4 d-none">Sin registros de cambios.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

const csrfToken     = '{{ csrf_token() }}';
const isAdminTenant = {{ auth()->user()->admin_tenant ? 'true' : 'false' }};
const isSupervisor  = {{ auth()->user()->role === 'supervisor' ? 'true' : 'false' }};
const canEditEmpleado = {{ (auth()->user()->admin_tenant || auth()->user()->editar_empleado) ? 'true' : 'false' }};
let currentPage = 1;
let sortBy  = 'name';
let sortDir = 'asc';

let deptoMap   = {};
let cargoMap   = {};
let sedeMap    = {};
let empresaMap = {};
let currentEmpleadoId = null;

async function fetchConteoUsuarios(empresaId) {
    const badge = document.getElementById('conteoUsuariosBadge');
    if (!empresaId) { badge.classList.add('d-none'); return; }
    try {
        const res = await fetch(`/admin/empleados/conteo-usuarios?empresa_id=${empresaId}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        badge.textContent = `Activos: ${data.activos} / ${data.max}`;
        badge.className = 'badge ms-2 ' + (data.activos >= data.max ? 'bg-danger' : 'bg-info');
        badge.classList.remove('d-none');
    } catch(e) { badge.classList.add('d-none'); }
}

function resetForm() {
    currentEmpleadoId = null;
    document.getElementById('btnGuardarEmpleado').onclick = () => saveEmpleado(null);
    document.getElementById('empleadoForm').reset();
    document.getElementById('empleadoId').value = '';
    document.getElementById('empPassword').required = true;
    document.getElementById('empRole').value = 'empleado';
    document.getElementById('empCodigoRow').style.display = 'none';
    document.getElementById('empActivo').checked = true;
    document.getElementById('empExportarEmpleados').checked = false;
    document.getElementById('empImportarEmpleados').checked = false;
    document.getElementById('empCrearEmpleado').checked = false;
    document.getElementById('empEditarEmpleado').checked = false;
    document.getElementById('empleadoModalTitle').textContent = 'Nuevo Usuario';
    document.getElementById('conteoUsuariosBadge').classList.add('d-none');
    document.getElementById('empTratamientoInfo').classList.add('d-none');
    document.querySelectorAll('.emp-sede-check').forEach(cb => cb.checked = false);
    if (isAdminTenant) {
        document.getElementById('empEmpresaId').value = '';
        renderSedeOptions([], []);
    } else {
        cargarLideres();
        fetchConteoUsuarios({{ auth()->user()->empresa_id ?? 'null' }});
    }
}

function initCatalogos() {
    if (isAdminTenant) {
        @foreach($empresas as $emp)
        empresaMap[{{ $emp->id }}] = '{{ addslashes($emp->nombre) }}';
        document.getElementById('empEmpresaId').innerHTML  += `<option value="{{ $emp->id }}">{{ $emp->nombre }}</option>`;
        document.getElementById('filterEmpresa').innerHTML += `<option value="{{ $emp->id }}">{{ $emp->nombre }}</option>`;
        @endforeach
        document.getElementById('empEmpresaId').addEventListener('change', e => loadCatalogosParaEmpresa(e.target.value));
        return;
    }

    poblarCatalogos({
        departamentos: @json($deptos),
        cargos:        @json($cargos),
        horarios:      @json($horarios),
        sedes:         @json($sedes),
        empleadores:   @json($empleadores),
    });
}

async function loadCatalogosParaEmpresa(empresaId) {
    if (!empresaId) { renderSedeOptions([], []); fetchConteoUsuarios(null); return; }

    const [resCat, resSedes] = await Promise.all([
        fetch(`/admin/empleados/tenant-catalogs?empresa_id=${empresaId}`),
        fetch(`/admin/empleados/tenant-sedes?empresa_id=${empresaId}`),
    ]);
    if (resCat.ok)   poblarCatalogos(await resCat.json(), true);
    if (resSedes.ok) {
        const sedes = (await resSedes.json()).data || [];
        sedeMap = { ...sedeMap, ...Object.fromEntries(sedes.map(s => [s.id, s.nombre])) };
        renderSedeOptions(sedes, []);
    }
    await cargarLideres(empresaId);
    fetchConteoUsuarios(empresaId);
}

async function cargarLideres(empresaId = null, selectedId = null) {
    const url = empresaId
        ? `/admin/empleados/lideres?empresa_id=${empresaId}`
        : '/admin/empleados/lideres';
    const res = await fetch(url);
    if (!res.ok) return;
    const lideres = (await res.json()).data || [];
    const sel = document.getElementById('empLider');
    sel.innerHTML = '<option value="">— Sin líder —</option>';
    lideres.forEach(l => {
        sel.innerHTML += `<option value="${l.id}" ${l.id == selectedId ? 'selected' : ''}>${l.name}${l.codigo_empleado ? ' ('+l.codigo_empleado+')' : ''}</option>`;
    });
}

function poblarCatalogos(data, soloModal = false) {
    const deptos      = data.departamentos || [];
    const cargos      = data.cargos        || [];
    const horarios    = data.horarios      || [];
    const sedes       = data.sedes         || [];
    const empleadores = data.empleadores   || [];

    deptoMap = Object.fromEntries(deptos.map(d => [d.id, d.nombre]));
    cargoMap = Object.fromEntries(cargos.map(c => [c.id, c.nombre]));
    sedeMap  = Object.fromEntries(sedes.map(s => [s.id, s.nombre]));

    const selDepto     = document.getElementById('empDepartamento');
    const selCargo     = document.getElementById('empCargo');
    const selHorario   = document.getElementById('empHorario');
    const selEmpleador = document.getElementById('empEmpleador');
    selDepto.innerHTML     = '<option value="">— Sin departamento —</option>';
    selCargo.innerHTML     = '<option value="">— Sin cargo —</option>';
    selHorario.innerHTML   = '<option value="">— Sin horario —</option>';
    selEmpleador.innerHTML = '<option value="">— Sin empleador —</option>';

    deptos.forEach(d => selDepto.innerHTML     += `<option value="${d.id}">${d.nombre}</option>`);
    cargos.forEach(c => selCargo.innerHTML     += `<option value="${c.id}">${c.nombre}</option>`);
    horarios.forEach(h => selHorario.innerHTML += `<option value="${h.id}">${h.nombre}</option>`);
    empleadores.forEach(e => selEmpleador.innerHTML += `<option value="${e.id}">${e.nombre}</option>`);

    if (!soloModal) {
        renderSedeOptions(sedes, []);
        const filterDepto = document.getElementById('filterDepto');
        filterDepto.innerHTML = '<option value="">Todos los deptos.</option>';
        deptos.forEach(d => filterDepto.innerHTML += `<option value="${d.id}">${d.nombre}</option>`);
    }
}

function renderSedeOptions(sedes, selectedIds = []) {
    const container  = document.getElementById('empSedesContainer');
    const vacio      = document.getElementById('empSedesVacio');
    const filterSede = document.getElementById('filterSede');

    filterSede.innerHTML = '<option value="">Todas las sedes</option>';

    if (!sedes.length) {
        container.innerHTML = '<span class="text-muted small" id="empSedesVacio">Sin sedes disponibles</span>';
        return;
    }

    container.innerHTML = sedes.map(s => `
        <div class="form-check">
            <input class="form-check-input emp-sede-check" type="checkbox"
                   value="${s.id}" id="sedeCheck${s.id}"
                   ${selectedIds.includes(s.id) ? 'checked' : ''}>
            <label class="form-check-label" for="sedeCheck${s.id}">${s.nombre}</label>
        </div>
    `).join('');

    sedes.forEach(s => {
        filterSede.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
    });
}

function getSelectedSedeIds() {
    return [...document.querySelectorAll('.emp-sede-check:checked')]
        .map(cb => parseInt(cb.value));
}

async function loadSedesParaEmpresa(empresaId, selectedIds = []) {
    const container = document.getElementById('empSedesContainer');
    container.innerHTML = '<span class="text-muted small">Cargando sedes...</span>';
    if (!empresaId) { renderSedeOptions([], []); return; }
    try {
        const res   = await fetch(`/admin/empleados/tenant-sedes?empresa_id=${empresaId}`);
        const sedes = (await res.json()).data || [];
        sedeMap = { ...sedeMap, ...Object.fromEntries(sedes.map(s => [s.id, s.nombre])) };
        renderSedeOptions(sedes, selectedIds);
    } catch (e) {
        container.innerHTML = '<span class="text-danger small">Error al cargar sedes</span>';
    }
}

async function loadEmpleados(page = 1) {
    currentPage = page;
    const search    = document.getElementById('filterSearch').value;
    const deptoId   = document.getElementById('filterDepto').value;
    const sedeId    = document.getElementById('filterSede').value;
    const empresaId = isAdminTenant ? document.getElementById('filterEmpresa').value : '';
    const perPage = document.getElementById('perPageSelect').value;
    let url = `/admin/empleados/list?page=${page}&per_page=${perPage}&sort=${sortBy}&dir=${sortDir}`;
    if (search)    url += `&search=${encodeURIComponent(search)}`;
    if (deptoId)   url += `&departamento_id=${deptoId}`;
    if (sedeId)    url += `&sede_id=${sedeId}`;
    if (empresaId) url += `&empresa_id=${empresaId}`;

    try {
        const res = await fetch(url);
        if (!res.ok) {
            document.getElementById('empleadosTbody').innerHTML = `<tr><td colspan="12" class="text-center text-danger py-3">Error ${res.status} al cargar empleados</td></tr>`;
            return;
        }
        const data = await res.json();
        const tbody = document.getElementById('empleadosTbody');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">Sin usuarios</td></tr>';
        } else {
            tbody.innerHTML = data.data.map(e => {
                const rolBadge = e.role === 'admin'
                    ? `<span class="badge bg-warning text-dark">${e.admin_tenant ? 'Admin multi-empresa' : 'Admin'}</span>`
                    : e.role === 'supervisor'
                        ? '<span class="badge bg-info text-dark">Supervisor</span>'
                        : '<span class="badge bg-secondary">Empleado</span>';
                return `
                <tr>
                    <td><span class="badge bg-primary">${e.codigo_empleado || '—'}</span></td>
                    <td><strong>${e.name || 'N/A'}</strong></td>
                    <td>${e.cedula || '—'}</td>
                    <td>${e.email || 'N/A'}</td>
                    ${isAdminTenant ? `<td><span class="badge bg-light text-dark border">${empresaMap[e.empresa_id] || '—'}</span></td>` : ''}
                    <td>${rolBadge}</td>
                    <td>
                        <div>${e.cargo_nombre || (e.cargo_id ? cargoMap[e.cargo_id] : null) || '—'}</div>
                        <small class="text-muted">${e.departamento_nombre || (e.departamento_id ? deptoMap[e.departamento_id] : null) || ''}</small>
                    </td>
                    <td>${(e.sede_nombres && e.sede_nombres.length) ? e.sede_nombres.map(n => `<span class="badge bg-info text-dark me-1">${n}</span>`).join('') : (e.sede_ids && e.sede_ids.length) ? e.sede_ids.map(id => `<span class="badge bg-info text-dark me-1">${sedeMap[id] || id}</span>`).join('') : '—'}</td>
                    <td><span class="badge ${e.is_active ? 'bg-success' : 'bg-danger'}">${e.is_active ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <button class="btn btn-sm ${e.has_face_descriptor ? 'btn-success' : 'btn-outline-secondary'}" onclick="verRostros(${e.id}, '${(e.name||'').replace(/'/g,'')}')">
                            <i class="fa-solid fa-face-smile"></i>
                            <span class="ms-1">${e.has_face_descriptor ? '✓' : '—'}</span>
                        </button>
                    </td>
                    <td>
                        <div class="d-flex flex-nowrap gap-1">
                            <button class="btn btn-sm btn-outline-primary" onclick="this.disabled=true;editEmpleado(${e.id}, '${e.encrypted_id}').finally(()=>this.disabled=false)" ${canEditEmpleado ? '' : 'disabled'}><i class="fa-solid fa-pen"></i></button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verLogEmpleado(${e.id})" title="Ver historial de cambios"><i class="fa-solid fa-clock-rotate-left"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteEmpleado('${e.encrypted_id}')" ${isSupervisor ? 'disabled' : ''}><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }
        document.getElementById('empleadosInfo').textContent = `${data.total || 0} usuarios`;
        renderPagination(data);
    } catch(e) { console.error(e); }
}

function renderPagination(data) {
    const nav = document.getElementById('empleadosPagination');
    if (!data.last_page || data.last_page <= 1) { nav.innerHTML = ''; return; }
    const cur = data.current_page;
    const last = data.last_page;
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    html += `<li class="page-item ${cur === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadEmpleados(${cur - 1});return false">&#8249; Anterior</a></li>`;
    for (let i = 1; i <= last; i++) {
        html += `<li class="page-item ${i === cur ? 'active' : ''}"><a class="page-link" href="#" onclick="loadEmpleados(${i});return false">${i}</a></li>`;
    }
    html += `<li class="page-item ${cur === last ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadEmpleados(${cur + 1});return false">Siguiente &#8250;</a></li>`;
    html += '</ul></nav>';
    nav.innerHTML = html;
}

async function editEmpleado(id, encryptedId) {
    const loader = document.getElementById('empleadoModalLoader');
    const form   = document.getElementById('empleadoForm');
    loader.classList.remove('d-none');
    form.classList.add('d-none');
    document.getElementById('empleadoModalTitle').textContent = 'Cargando...';
    new bootstrap.Modal(document.getElementById('empleadoModal')).show();
    try {
        const res = await fetch(`/admin/empleados/${id}/detail`);
        if (!res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('empleadoModal')).hide();
            alert('Error ' + res.status + ' al cargar empleado');
            return;
        }
        const data = await res.json();
        const e = data.data;
        currentEmpleadoId = encryptedId || e.encrypted_id;
        document.getElementById('btnGuardarEmpleado').onclick = () => saveEmpleado(currentEmpleadoId);
        document.getElementById('empleadoId').value      = currentEmpleadoId;

        if (isAdminTenant) {
            const selEmp = document.getElementById('empEmpresaId');
            selEmp.value = e.empresa_id || '';
            selEmp.disabled = false;
            selEmp.title = '';
            // Verificar movimientos en background (no bloquea apertura del modal)
            fetch(`/admin/empleados/${id}/movimientos`)
                .then(r => r.json())
                .then(m => {
                    if (m.tiene_movimientos) {
                        selEmp.disabled = true;
                        selEmp.title = 'No se puede cambiar: el empleado tiene registros de asistencia';
                    }
                });
            // Cargar catálogos y sedes del tenant de la empresa del empleado
            await loadCatalogosParaEmpresa(e.empresa_id);
            await loadSedesParaEmpresa(e.empresa_id, e.sede_ids || []);
            await cargarLideres(e.empresa_id, e.lider_id);
        } else {
            // Para usuarios no-admin_tenant la empresa se toma del usuario logueado, no se muestra
            await cargarLideres(null, e.lider_id);
        }

        document.getElementById('empName').value         = e.name || '';
        document.getElementById('empEmail').value        = e.email || '';
        document.getElementById('empPassword').value     = '';
        document.getElementById('empPassword').required  = false;
        document.getElementById('empCodigo').value        = e.codigo_empleado;
        document.getElementById('empCodigoRow').style.display = '';
        document.getElementById('empCedula').value       = e.cedula || '';
        document.getElementById('empTelefono').value     = e.telefono || '';
        document.getElementById('empDepartamento').value = e.departamento_id || '';
        document.getElementById('empCargo').value        = e.cargo_id || '';
        document.getElementById('empHorario').value      = e.horario_id || '';
        document.getElementById('empEmpleador').value    = e.empleador_id || '';
        if (!isAdminTenant) document.getElementById('empLider').value = e.lider_id || '';
        // Para admin no-tenant, marcar las sedes ya asignadas
        if (!isAdminTenant) {
            document.querySelectorAll('.emp-sede-check').forEach(cb => {
                cb.checked = (e.sede_ids || []).includes(parseInt(cb.value));
            });
        }
        document.getElementById('empCentroCosto').value  = e.centro_costo || '';
        document.getElementById('empRuta').value         = e.ruta || '';
        document.getElementById('empRole').value         = e.role || 'empleado';
        document.getElementById('empActivo').checked     = !!e.is_active;
        document.getElementById('empExportarEmpleados').checked = !!e.exportar_empleados;
        document.getElementById('empImportarEmpleados').checked = !!e.importar_empleados;
        document.getElementById('empCrearEmpleado').checked = !!e.crear_empleado;
        document.getElementById('empEditarEmpleado').checked = !!e.editar_empleado;
        document.getElementById('empleadoModalTitle').textContent = 'Editar Usuario';

        // Tratamiento de datos
        const tratWrap  = document.getElementById('empTratamientoInfo');
        const tratAlert = document.getElementById('empTratamientoAlert');
        const tratIcon  = document.getElementById('empTratamientoIcon');
        const tratTit   = document.getElementById('empTratamientoTitulo');
        const tratDet   = document.getElementById('empTratamientoDetalle');
        tratAlert.className = 'alert d-flex align-items-start gap-2 py-2 px-3 mb-3';
        if (!e.tratamiento_datos) {
            tratAlert.classList.add('alert-secondary');
            tratIcon.textContent = '🔒';
            tratTit.textContent  = 'Sin tratamiento de datos';
            tratDet.textContent  = 'El usuario aún no ha aceptado la política de tratamiento de datos.';
        } else {
            const dias     = e.dias_tratamiento_dato ?? 365;
            const aceptoMs = e.tratamiento_datos_at ? new Date(e.tratamiento_datos_at).getTime() : null;
            if (!aceptoMs) {
                tratAlert.classList.add('alert-warning');
                tratIcon.textContent = '⚠️';
                tratTit.textContent  = 'Tratamiento aceptado (fecha desconocida)';
                tratDet.textContent  = `Vigencia configurada: ${dias} día(s).`;
            } else {
                const vence     = new Date(aceptoMs + dias * 86400000);
                const hoy       = new Date();
                const diffMs    = vence - hoy;
                const diffDias  = Math.ceil(diffMs / 86400000);
                const fmtVence  = vence.toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' });
                if (diffDias < 0) {
                    tratAlert.classList.add('alert-danger');
                    tratIcon.textContent = '❌';
                    tratTit.textContent  = 'Tratamiento VENCIDO';
                    tratDet.textContent  = `Venció el ${fmtVence} (hace ${Math.abs(diffDias)} día(s)). Debe renovarlo al iniciar sesión.`;
                } else if (diffDias <= 30) {
                    tratAlert.classList.add('alert-warning');
                    tratIcon.textContent = '⚠️';
                    tratTit.textContent  = `Tratamiento próximo a vencer — ${diffDias} día(s) restantes`;
                    tratDet.textContent  = `Vence el ${fmtVence}.`;
                } else {
                    tratAlert.classList.add('alert-success');
                    tratIcon.textContent = '✅';
                    tratTit.textContent  = `Tratamiento vigente — ${diffDias} día(s) restantes`;
                    tratDet.textContent  = `Vence el ${fmtVence}.`;
                }
            }
        }
        tratWrap.classList.remove('d-none');

        fetchConteoUsuarios(e.empresa_id || {{ auth()->user()->empresa_id ?? 'null' }});
        loader.classList.add('d-none');
        form.classList.remove('d-none');
    } catch(e) {
        bootstrap.Modal.getInstance(document.getElementById('empleadoModal')).hide();
        console.error(e);
    }
}

async function saveEmpleado(id) {
    if (id === undefined) id = currentEmpleadoId || document.getElementById('empleadoId').value || null;
    const deptoVal     = document.getElementById('empDepartamento').value;
    const cargoVal     = document.getElementById('empCargo').value;
    const horarioVal   = document.getElementById('empHorario').value;
    if (!horarioVal) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debes seleccionar un horario para el empleado.' });
        return;
    }
    const empleadorVal = document.getElementById('empEmpleador').value;
    const payload = {
        name:             document.getElementById('empName').value,
        email:            document.getElementById('empEmail').value,
        cedula:           document.getElementById('empCedula').value,
        telefono:         document.getElementById('empTelefono').value,
        departamento_id:  deptoVal     ? parseInt(deptoVal)     : null,
        cargo_id:         cargoVal     ? parseInt(cargoVal)     : null,
        horario_id:       horarioVal   ? parseInt(horarioVal)   : null,
        empleador_id:     empleadorVal ? parseInt(empleadorVal) : null,
        lider_id:         document.getElementById('empLider').value ? parseInt(document.getElementById('empLider').value) : null,
        centro_costo:     document.getElementById('empCentroCosto').value || null,
        ruta:             document.getElementById('empRuta').value || null,
        sede_ids:         getSelectedSedeIds(),
        role:             document.getElementById('empRole').value,
        is_active:        document.getElementById('empActivo').checked,
        exportar_empleados: document.getElementById('empExportarEmpleados').checked,
        importar_empleados: document.getElementById('empImportarEmpleados').checked,
        crear_empleado:     document.getElementById('empCrearEmpleado').checked,
        editar_empleado:    document.getElementById('empEditarEmpleado').checked,
    };
    if (isAdminTenant) {
        const selEmp = document.getElementById('empEmpresaId');
        if (!selEmp.disabled) {
            payload.empresa_id = selEmp.value ? parseInt(selEmp.value) : null;
        }
    }
    const pass = document.getElementById('empPassword').value;
    if (pass) payload.password = pass;
    if (!id) payload.password = pass || 'password123';

    const url    = id ? `/admin/empleados/${id}/update` : '/admin/empleados';
    const method = 'POST';
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };

    try {
        const res = await fetch(url, { method, headers, body: JSON.stringify(payload) });
        const text = await res.text();
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('empleadoModal')).hide();
            loadEmpleados(currentPage);
            Swal.fire({ icon: 'success', title: 'Guardado', text: 'Empleado actualizado correctamente.', timer: 1800, showConfirmButton: false });
        } else {
            try {
                const err = JSON.parse(text);
                const msg = Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            } catch(_) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error del servidor: ' + res.status });
            }
        }
    } catch(e) { console.error('[saveEmpleado] excepción:', e); }
}

async function deleteEmpleado(id) {
    const result = await Swal.fire({
        title: '¿Desactivar empleado?',
        text: 'El empleado quedará inactivo. Puedes reactivarlo después.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;
    try {
        const res = await fetch(`/admin/empleados/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (res.ok) {
            loadEmpleados(currentPage);
            Swal.fire({ icon: 'success', title: 'Desactivado', text: 'El empleado ha sido desactivado correctamente.', timer: 1800, showConfirmButton: false });
        } else {
            const text = await res.text();
            let msg = 'Error ' + res.status;
            try { const err = JSON.parse(text); msg = err.message || msg; } catch(_) {}
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        }
    } catch(e) { console.error(e); }
}

// ── Rostros ──────────────────────────────────────────────────────────────────
let rostrosEmpleadoId = null;

async function verRostros(empleadoId, nombre) {
    rostrosEmpleadoId = empleadoId;
    document.getElementById('rostrosNombre').textContent = nombre;
    document.getElementById('rostrosContent').innerHTML = '<div class="py-3">Cargando...</div>';
    new bootstrap.Modal(document.getElementById('rostrosModal')).show();
    await cargarRostros();
}

async function cargarRostros() {
    try {
        const res = await fetch(`/admin/empleados/${rostrosEmpleadoId}/imagenes-rostro?con_imagen=true`);
        const data = await res.json();
        const imgs = data.data || [];
        const container = document.getElementById('rostrosContent');

        if (imgs.length === 0) {
            container.innerHTML = `
                <div class="py-4 text-muted">
                    <i class="fa-solid fa-face-meh fa-2x mb-2 d-block"></i>
                    No hay rostros registrados para este empleado.
                </div>`;
            return;
        }

        container.innerHTML = `
            <p class="text-muted mb-3">${imgs.length}/5 imágenes registradas</p>
            <div class="row g-3" id="rostrosGrid"></div>`;

        const grid = document.getElementById('rostrosGrid');
        imgs.forEach(img => {
            const src = img.imagen_base64 || '';
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4';
            col.innerHTML = `
                <div class="card h-100 border">
                    <div class="position-relative">
                        ${src
                            ? `<img src="${src}" class="card-img-top" style="height:180px;object-fit:cover;border-radius:4px 4px 0 0;image-orientation:from-image;" alt="Rostro ${img.orden}">`
                            : `<div class="d-flex align-items-center justify-content-center bg-light" style="height:180px;">
                                   <i class="fa-solid fa-image fa-3x text-muted"></i>
                               </div>`
                        }
                        <span class="badge bg-primary position-absolute top-0 start-0 m-2">#${img.orden}</span>
                    </div>
                    <div class="card-body p-2 text-center">
                        <button class="btn btn-sm btn-outline-danger w-100" onclick="eliminarRostro(${img.id})">
                            <i class="fa-solid fa-trash me-1"></i> Eliminar
                        </button>
                    </div>
                </div>`;
            grid.appendChild(col);
        });
    } catch(e) {
        document.getElementById('rostrosContent').innerHTML =
            `<div class="text-danger py-3">Error al cargar: ${e.message}</div>`;
    }
}

async function eliminarRostro(imageId) {
    if (!confirm('¿Eliminar esta imagen de rostro?')) return;
    try {
        const res = await fetch(`/admin/empleados/${rostrosEmpleadoId}/imagenes-rostro/${imageId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        if (res.ok) {
            await cargarRostros();
            loadEmpleados(currentPage); // actualizar badge en tabla
        } else {
            const err = await res.json();
            alert(err.message || 'Error al eliminar');
        }
    } catch(e) { alert('Error: ' + e.message); }
}

// ── Export Empleados ──────────────────────────────────────────────────────────
function exportarEmpleados() {
    let url = '{{ route('admin.empleados.export') }}';
    @if(auth()->user()->admin_tenant)
    const empresaId = document.getElementById('filterEmpresa').value;
    if (!empresaId) {
        alert('Selecciona una empresa antes de exportar.');
        return;
    }
    url += `?empresa_id=${empresaId}`;
    @endif
    window.location.href = url;
}

// ── Import Empleados ──────────────────────────────────────────────────────────
function openImportEmpleados() {
    document.getElementById('importEmpleadosFile').value = '';
    const result = document.getElementById('importEmpleadosResult');
    result.style.display = 'none';
    result.innerHTML = '';
    new bootstrap.Modal(document.getElementById('importEmpleadosModal')).show();
}

@if(auth()->user()->admin_tenant)
document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'importEmpresaId') {
        const empresaId = e.target.value;
        const link = document.getElementById('importEmpleadosTemplateLink');
        link.href = empresaId
            ? `/admin/empleados/template?empresa_id=${empresaId}`
            : '{{ route('admin.empleados.template') }}';
    }
});
@endif

async function ejecutarImportEmpleados() {
    const fileInput = document.getElementById('importEmpleadosFile');
    const result    = document.getElementById('importEmpleadosResult');

    @if(auth()->user()->admin_tenant)
    const empresaId = document.getElementById('importEmpresaId').value;
    if (!empresaId) {
        result.style.display = '';
        result.innerHTML = '<div class="alert alert-warning mb-0">Selecciona una empresa antes de importar.</div>';
        return;
    }
    @endif

    if (!fileInput.files.length) {
        result.style.display = '';
        result.innerHTML = '<div class="alert alert-warning mb-0">Selecciona un archivo Excel para importar.</div>';
        return;
    }

    const btn       = document.getElementById('importEmpleadosSubmitBtn');
    const cancelBtn = document.getElementById('importEmpleadosCancelBtn');
    btn.disabled       = true;
    cancelBtn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importando...';
    result.style.display = 'none';

    try {
        const fd = new FormData();
        fd.append('file', fileInput.files[0]);
        @if(auth()->user()->admin_tenant)
        fd.append('empresa_id', document.getElementById('importEmpresaId').value);
        @endif

        const res  = await fetch('/admin/empleados/import', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: fd,
        });
        const data = await res.json();

        if (res.ok) {
            const listHtml = (label, color, icon, list) => list.length
                ? `<details class="mt-1"><summary class="text-${color} small fw-semibold" style="cursor:pointer;">
                    <i class="fa-solid ${icon} me-1"></i>${label} (${list.length})</summary>
                    <ul class="mb-0 mt-1 ps-3 small">${list.map(e => `<li>${e}</li>`).join('')}</ul>
                   </details>` : '';

            let html = `<div class="alert alert-success mb-2">
                <strong>Importación completada</strong><br>
                <i class="fa-solid fa-plus-circle text-success me-1"></i>Creados: <strong>${data.created}</strong>
                &nbsp;|&nbsp;
                <i class="fa-solid fa-pen text-primary me-1"></i>Actualizados: <strong>${data.updated}</strong>
                &nbsp;|&nbsp;
                <i class="fa-solid fa-ban text-warning me-1"></i>Omitidos: <strong>${data.skipped}</strong>
            </div>`;
            html += listHtml('Creados', 'success', 'fa-plus-circle', data.createdList || []);
            html += listHtml('Actualizados', 'primary', 'fa-pen', data.updatedList || []);
            html += listHtml('Omitidos', 'warning', 'fa-ban', data.skippedList || []);
            result.innerHTML = html;
            loadEmpleados();
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('<br>')
                : (data.message || 'Error al importar');
            result.innerHTML = `<div class="alert alert-danger mb-0">${msg}</div>`;
        }
    } catch(e) {
        result.innerHTML = `<div class="alert alert-danger mb-0">Error: ${e.message}</div>`;
    } finally {
        result.style.display = '';
        btn.disabled       = false;
        cancelBtn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-file-import me-1"></i> Importar';
    }
}

// ── Log de cambios ────────────────────────────────────────────────────────────
const LOG_LABELS_EMP = {
    name: 'Nombre', cedula: 'Cédula', email: 'Email', role: 'Rol', tipo: 'Tipo',
    admin_tenant: 'Admin multi-empresa', is_active: 'Activo', empresa_id: 'Empresa',
    empleador_id: 'Empleador', lider_id: 'Líder', codigo_empleado: 'Código',
    departamento_id: 'Departamento', cargo_id: 'Cargo', horario_id: 'Horario', telefono: 'Teléfono',
    centro_costo: 'Centro de costo', ruta: 'Ruta',
    exportar_empleados: 'Exportar empleados', importar_empleados: 'Importar empleados',
    crear_empleado: 'Crear empleado', editar_empleado: 'Editar empleado'
};

async function verLogEmpleado(id) {
    var spinner = document.getElementById('logEmpleadoSpinner');
    var content = document.getElementById('logEmpleadoContent');
    var tbody   = document.getElementById('logEmpleadoTbody');
    var vacio   = document.getElementById('logEmpleadoVacio');

    spinner.classList.remove('d-none');
    content.classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalLogEmpleado')).show();

    var res  = await fetch('/admin/empleados/' + id + '/log');
    var logs = await res.json();

    spinner.classList.add('d-none');
    content.classList.remove('d-none');

    if (!logs.length) {
        tbody.innerHTML = '';
        vacio.classList.remove('d-none');
        return;
    }
    vacio.classList.add('d-none');

    var rows = [];
    logs.forEach(function(log) {
        var badgeClass = log.evento === 'DELETE' ? 'bg-danger' : 'bg-warning text-dark';
        var anterior = log.anterior || {};
        var nuevo    = log.nuevo    || {};
        var campos   = Object.keys(Object.assign({}, anterior, nuevo));
        var cambios  = log.evento === 'DELETE'
            ? campos
            : campos.filter(function(k) {
                var a = anterior[k] == null ? '' : String(anterior[k]);
                var b = nuevo[k]    == null ? '' : String(nuevo[k]);
                return a !== b;
            });
        if (!cambios.length) return;

        cambios.forEach(function(campo, i) {
            rows.push(
                '<tr>' +
                (i === 0
                    ? '<td rowspan="' + cambios.length + '" class="align-middle small text-muted">' + (log.created_at || '') + '</td>' +
                      '<td rowspan="' + cambios.length + '" class="align-middle"><span class="badge ' + badgeClass + '">' + log.evento + '</span></td>' +
                      '<td rowspan="' + cambios.length + '" class="align-middle small">' + log.usuario + '</td>'
                    : '') +
                '<td class="small fw-semibold">' + (LOG_LABELS_EMP[campo] || campo) + '</td>' +
                '<td class="small text-danger">' + (anterior[campo] != null ? anterior[campo] : '—') + '</td>' +
                '<td class="small text-success">' + (nuevo[campo]    != null ? nuevo[campo]    : '—') + '</td>' +
                '</tr>'
            );
        });
    });

    tbody.innerHTML = rows.join('');
}

function updateSortIcons() {
    document.querySelectorAll('th.sortable').forEach(th => {
        th.querySelectorAll('i.sort-icon').forEach(i => i.remove());
        const icon = document.createElement('i');
        if (th.dataset.col === sortBy) {
            icon.className = `sort-icon fa-solid fa-sort-${sortDir === 'asc' ? 'up' : 'down'} ms-1 text-primary`;
        } else {
            icon.className = 'sort-icon fa-solid fa-sort ms-1 text-muted opacity-50';
        }
        th.appendChild(icon);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('th.sortable').forEach(th => {
        th.style.cursor = 'pointer';
        th.style.userSelect = 'none';
        th.addEventListener('click', () => {
            if (sortBy === th.dataset.col) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy  = th.dataset.col;
                sortDir = 'asc';
            }
            updateSortIcons();
            loadEmpleados(1);
        });
    });
    updateSortIcons();
    initCatalogos();
    loadEmpleados();
});
</script>
@endpush
