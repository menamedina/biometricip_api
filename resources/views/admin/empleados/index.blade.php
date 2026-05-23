@extends('layouts.admin')
@section('title', 'Empleados')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
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
                                <th>Departamento</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="empleadosTbody">
                            <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
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
                            <input type="text" id="empDepartamento" class="form-control" list="deptoList">
                            <datalist id="deptoList"></datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" id="empCargo" class="form-control">
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
let currentPage = 1;

function resetForm() {
    document.getElementById('empleadoForm').reset();
    document.getElementById('empleadoId').value = '';
    document.getElementById('empPassword').required = false;
    document.getElementById('empleadoModalTitle').textContent = 'Nuevo Empleado';
}

async function loadEmpleados(page = 1) {
    currentPage = page;
    const search = document.getElementById('filterSearch').value;
    const depto = document.getElementById('filterDepto').value;
    let url = `/api/empleados?page=${page}&per_page=15`;
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (depto) url += `&departamento=${encodeURIComponent(depto)}`;

    try {
        const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const tbody = document.getElementById('empleadosTbody');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin empleados</td></tr>';
        } else {
            tbody.innerHTML = data.data.map(e => `
                <tr>
                    <td><span class="badge bg-primary">${e.codigo_empleado}</span></td>
                    <td><strong>${e.user?.name || 'N/A'}</strong></td>
                    <td>${e.user?.email || 'N/A'}</td>
                    <td>${e.departamento || '—'}</td>
                    <td>${e.cargo || '—'}</td>
                    <td><span class="badge ${e.is_active ? 'bg-success' : 'bg-danger'}">${e.is_active ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editEmpleado(${e.id})"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteEmpleado(${e.id})"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }
        document.getElementById('empleadosInfo').textContent = `${data.total || 0} empleados`;
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
        const res = await fetch(`/api/empleados/${id}`, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const e = data.data;
        document.getElementById('empleadoId').value = e.id;
        document.getElementById('empName').value = e.user?.name || '';
        document.getElementById('empEmail').value = e.user?.email || '';
        document.getElementById('empPassword').value = '';
        document.getElementById('empPassword').required = false;
        document.getElementById('empCodigo').value = e.codigo_empleado;
        document.getElementById('empTelefono').value = e.telefono || '';
        document.getElementById('empDepartamento').value = e.departamento || '';
        document.getElementById('empCargo').value = e.cargo || '';
        document.getElementById('empleadoModalTitle').textContent = 'Editar Empleado';
        new bootstrap.Modal(document.getElementById('empleadoModal')).show();
    } catch(e) { console.error(e); }
}

async function saveEmpleado() {
    const id = document.getElementById('empleadoId').value;
    const payload = {
        name: document.getElementById('empName').value,
        email: document.getElementById('empEmail').value,
        codigo_empleado: document.getElementById('empCodigo').value,
        telefono: document.getElementById('empTelefono').value,
        departamento: document.getElementById('empDepartamento').value,
        cargo: document.getElementById('empCargo').value,
    };
    const pass = document.getElementById('empPassword').value;
    if (pass) payload.password = pass;
    if (!id) payload.password = pass || 'password123';

    const url = id ? `/api/empleados/${id}` : '/api/empleados';
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

async function loadDeptos() {
    try {
        const res = await fetch('/api/empleados/departamentos/list', { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const sel = document.getElementById('filterDepto');
        const datalist = document.getElementById('deptoList');
        (data.data || []).forEach(d => {
            sel.innerHTML += `<option value="${d}">${d}</option>`;
            datalist.innerHTML += `<option value="${d}">`;
        });
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', () => { loadEmpleados(); loadDeptos(); });
</script>
@endpush
