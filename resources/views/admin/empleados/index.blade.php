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
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empleadoModal" onclick="resetForm()">
                    <i class="fa-solid fa-plus me-1"></i> Nuevo Empleado
                </button>
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
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="filterDepto" onchange="loadEmpleados()">
                                <option value="">Todos los deptos.</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Departamento</th>
                                <th>Cargo</th>
                                <th>Sede</th>
                                <th>Estado</th>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
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
                        @else
                        <div class="col-md-12 mb-3" id="empEmpresaRow" style="display:none">
                            <label class="form-label">Empresa</label>
                            <input type="text" id="empEmpresa" class="form-control" readonly disabled style="background-color: #f8f9fa; font-weight: 600;">
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" id="empName" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="empEmail" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Contraseña <small class="text-muted">(dejar vacío para mantener)</small></label>
                            <input type="password" id="empPassword" class="form-control" minlength="6">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Código Empleado</label>
                            <input type="text" id="empCodigo" class="form-control" required>
                        </div>
                        <div class="col-md-5 mb-3">
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
                            <label class="form-label">Horario</label>
                            <select id="empHorario" class="form-select">
                                <option value="">— Sin horario —</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sede</label>
                            <select id="empSede" class="form-select">
                                <option value="">— Sin sede —</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Rol</label>
                            <select id="empRole" class="form-select">
                                <option value="empleado">Empleado</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empAdminTenant">
                                <label class="form-check-label">Admin multi-empresa</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="empActivo" checked>
                                <label class="form-check-label">Activo</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveEmpleado()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
const isAdminTenant = {{ auth()->user()->admin_tenant ? 'true' : 'false' }};
let currentPage = 1;

// Catálogos en memoria para resolver nombres
let deptoMap  = {};
let cargoMap  = {};
let sedeMap   = {};

function resetForm() {
    document.getElementById('empleadoForm').reset();
    document.getElementById('empleadoId').value = '';
    document.getElementById('empPassword').required = false;
    document.getElementById('empRole').value = 'empleado';
    document.getElementById('empAdminTenant').checked = false;
    document.getElementById('empActivo').checked = true;
    document.getElementById('empleadoModalTitle').textContent = 'Nuevo Usuario';
    if (isAdminTenant) {
        document.getElementById('empEmpresaId').value = '';
    }
}

async function loadCatalogos() {
    // Cargar lista de empresas si es admin_tenant
    if (isAdminTenant) {
        const resEmp = await fetch('/api/empresas', { headers: { 'Authorization': `Bearer ${token}` } });
        const dataEmp = await resEmp.json();
        const selEmp = document.getElementById('empEmpresaId');
        (dataEmp.data || []).forEach(e => {
            selEmp.innerHTML += `<option value="${e.id}">${e.nombre}</option>`;
        });
    }

    const res  = await fetch('/api/catalogos', { headers: { 'Authorization': `Bearer ${token}` } });
    const data = await res.json();

    const deptos   = data.departamentos || [];
    const cargos   = data.cargos        || [];
    const horarios = data.horarios      || [];
    const sedes    = data.sedes         || [];

    // Mapas id → nombre para resolver en tabla
    deptoMap = Object.fromEntries(deptos.map(d => [d.id, d.nombre]));
    cargoMap = Object.fromEntries(cargos.map(c => [c.id, c.nombre]));
    sedeMap  = Object.fromEntries(sedes.map(s => [s.id, s.nombre]));

    // Selects del modal
    const selDepto   = document.getElementById('empDepartamento');
    const selCargo   = document.getElementById('empCargo');
    const selHorario = document.getElementById('empHorario');
    const selSede    = document.getElementById('empSede');
    deptos.filter(d => d.is_active).forEach(d => {
        selDepto.innerHTML += `<option value="${d.id}">${d.nombre}</option>`;
    });
    cargos.filter(c => c.is_active).forEach(c => {
        selCargo.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
    });
    horarios.filter(h => h.is_active).forEach(h => {
        selHorario.innerHTML += `<option value="${h.id}">${h.nombre} (${h.hora_entrada?.slice(0,5)} - ${h.hora_salida?.slice(0,5)})</option>`;
    });
    sedes.forEach(s => {
        selSede.innerHTML += `<option value="${s.id}">${s.nombre}</option>`;
    });

    // Select del filtro
    const filterSel = document.getElementById('filterDepto');
    deptos.forEach(d => {
        filterSel.innerHTML += `<option value="${d.id}">${d.nombre}</option>`;
    });
}

async function loadEmpleados(page = 1) {
    currentPage = page;
    const search = document.getElementById('filterSearch').value;
    const deptoId = document.getElementById('filterDepto').value;
    let url = `/api/empleados?page=${page}&per_page=15`;
    if (search)  url += `&search=${encodeURIComponent(search)}`;
    if (deptoId) url += `&departamento_id=${deptoId}`;

    try {
        const res  = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const tbody = document.getElementById('empleadosTbody');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">Sin usuarios</td></tr>';
        } else {
            tbody.innerHTML = data.data.map(e => {
                const rolBadge = e.role === 'admin'
                    ? `<span class="badge bg-warning text-dark">${e.admin_tenant ? 'Admin multi-empresa' : 'Admin'}</span>`
                    : '<span class="badge bg-secondary">Empleado</span>';
                return `
                <tr>
                    <td><span class="badge bg-primary">${e.codigo_empleado || '—'}</span></td>
                    <td><strong>${e.name || 'N/A'}</strong></td>
                    <td>${e.email || 'N/A'}</td>
                    <td>${rolBadge}</td>
                    <td>${e.departamento_id ? (deptoMap[e.departamento_id] || e.departamento_id) : '—'}</td>
                    <td>${e.cargo_id ? (cargoMap[e.cargo_id] || e.cargo_id) : '—'}</td>
                    <td>${e.sede_id ? (sedeMap[e.sede_id] || e.sede_id) : '—'}</td>
                    <td><span class="badge ${e.is_active ? 'bg-success' : 'bg-danger'}">${e.is_active ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editEmpleado(${e.id})"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteEmpleado(${e.id})"><i class="fa-solid fa-trash"></i></button>
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
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    for (let i = 1; i <= data.last_page; i++) {
        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="loadEmpleados(${i});return false">${i}</a></li>`;
    }
    html += '</ul></nav>';
    nav.innerHTML = html;
}

async function editEmpleado(id) {
    try {
        const res  = await fetch(`/api/empleados/${id}`, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const e = data.data;
        document.getElementById('empleadoId').value      = e.id;
        if (isAdminTenant) {
            document.getElementById('empEmpresaId').value = e.empresa_id || '';
        } else if (e.empresa) {
            document.getElementById('empEmpresa').value = e.empresa;
            document.getElementById('empEmpresaRow').style.display = '';
        } else {
            document.getElementById('empEmpresaRow').style.display = 'none';
        }
        document.getElementById('empName').value         = e.name || '';
        document.getElementById('empEmail').value        = e.email || '';
        document.getElementById('empPassword').value     = '';
        document.getElementById('empPassword').required  = false;
        document.getElementById('empCodigo').value       = e.codigo_empleado;
        document.getElementById('empTelefono').value     = e.telefono || '';
        document.getElementById('empDepartamento').value = e.departamento_id || '';
        document.getElementById('empCargo').value        = e.cargo_id || '';
        document.getElementById('empHorario').value      = e.horario_id || '';
        document.getElementById('empSede').value         = e.sede_id || '';
        document.getElementById('empRole').value         = e.role || 'empleado';
        document.getElementById('empAdminTenant').checked = !!e.admin_tenant;
        document.getElementById('empActivo').checked     = !!e.is_active;
        document.getElementById('empleadoModalTitle').textContent = 'Editar Usuario';
        new bootstrap.Modal(document.getElementById('empleadoModal')).show();
    } catch(e) { console.error(e); }
}

async function saveEmpleado() {
    const id = document.getElementById('empleadoId').value;
    const deptoVal   = document.getElementById('empDepartamento').value;
    const cargoVal   = document.getElementById('empCargo').value;
    const horarioVal = document.getElementById('empHorario').value;
    const sedeVal    = document.getElementById('empSede').value;
    const payload = {
        name:             document.getElementById('empName').value,
        email:            document.getElementById('empEmail').value,
        codigo_empleado:  document.getElementById('empCodigo').value,
        telefono:         document.getElementById('empTelefono').value,
        departamento_id:  deptoVal   ? parseInt(deptoVal)   : null,
        cargo_id:         cargoVal   ? parseInt(cargoVal)   : null,
        horario_id:       horarioVal ? parseInt(horarioVal) : null,
        sede_id:          sedeVal    ? parseInt(sedeVal)     : null,
        role:             document.getElementById('empRole').value,
        admin_tenant:     document.getElementById('empAdminTenant').checked,
        is_active:        document.getElementById('empActivo').checked,
    };
    if (isAdminTenant) {
        const empIdVal = document.getElementById('empEmpresaId').value;
        payload.empresa_id = empIdVal ? parseInt(empIdVal) : null;
    }
    const pass = document.getElementById('empPassword').value;
    if (pass) payload.password = pass;
    if (!id) payload.password = pass || 'password123';

    const url    = id ? `/api/empleados/${id}` : '/api/empleados';
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('empleadoModal')).hide();
            loadEmpleados(currentPage);
        } else {
            const err = await res.json();
            alert(Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
        }
    } catch(e) { console.error(e); }
}

async function deleteEmpleado(id) {
    if (!confirm('¿Desactivar este empleado?')) return;
    try {
        await fetch(`/api/empleados/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
        loadEmpleados(currentPage);
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', () => { loadCatalogos().then(() => loadEmpleados()); });
</script>
@endpush
