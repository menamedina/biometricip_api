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
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="filterSede" onchange="loadEmpleados()">
                                <option value="">Todas las sedes</option>
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
                                <th>Cédula</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Departamento</th>
                                <th>Cargo</th>
                                <th>Sede</th>
                                <th>Estado</th>
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
                        <div class="col-md-3 mb-3" id="empCodigoRow" style="display:none">
                            <label class="form-label">Código Empleado</label>
                            <input type="text" id="empCodigo" class="form-control" readonly style="background-color:#f8f9fa;font-weight:600;">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cédula <span class="text-danger">*</span></label>
                            <input type="text" id="empCedula" class="form-control" required placeholder="Ej: 1234567890" inputmode="numeric">
                        </div>
                        <div class="col-md-4 mb-3">
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
                        <div class="col-12 mb-3">
                            <label class="form-label">Sedes asignadas</label>
                            <div id="empSedesContainer" class="border rounded p-2" style="min-height:42px; max-height:160px; overflow-y:auto; background:#fff;">
                                <span class="text-muted small" id="empSedesVacio">Sin sedes disponibles</span>
                            </div>
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
    document.getElementById('empPassword').required = true;
    document.getElementById('empRole').value = 'empleado';
    document.getElementById('empCodigoRow').style.display = 'none';
    document.getElementById('empActivo').checked = true;
    document.getElementById('empleadoModalTitle').textContent = 'Nuevo Usuario';
    // Desmarcar todos los checkboxes de sede
    document.querySelectorAll('.emp-sede-check').forEach(cb => cb.checked = false);
    if (isAdminTenant) {
        document.getElementById('empEmpresaId').value = '';
        renderSedeOptions([], []);
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
        // admin_tenant: recargar sedes al cambiar empresa
        selEmp.addEventListener('change', () => loadSedesParaEmpresa(selEmp.value));
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
    deptos.filter(d => d.is_active).forEach(d => {
        selDepto.innerHTML += `<option value="${d.id}">${d.nombre}</option>`;
    });
    cargos.filter(c => c.is_active).forEach(c => {
        selCargo.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
    });
    horarios.filter(h => h.is_active).forEach(h => {
        selHorario.innerHTML += `<option value="${h.id}">${h.nombre} (${h.hora_entrada?.slice(0,5)} - ${h.hora_salida?.slice(0,5)})</option>`;
    });

    // Sedes (modal + filtro de tabla)
    renderSedeOptions(sedes, []);

    // Select del filtro de departamento
    const filterDepto = document.getElementById('filterDepto');
    deptos.forEach(d => {
        filterDepto.innerHTML += `<option value="${d.id}">${d.nombre}</option>`;
    });
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

    if (!empresaId) {
        renderSedeOptions([], []);
        return;
    }

    try {
        const headers = { 'Authorization': `Bearer ${token}` };
        if (isAdminTenant) headers['X-Empresa-Id'] = empresaId;

        const res   = await fetch(`/api/sedes`, { headers });
        const data  = await res.json();
        const sedes = data.data || [];
        sedeMap = { ...sedeMap, ...Object.fromEntries(sedes.map(s => [s.id, s.nombre])) };
        renderSedeOptions(sedes, selectedIds);
    } catch (e) {
        container.innerHTML = '<span class="text-danger small">Error al cargar sedes</span>';
    }
}

async function loadEmpleados(page = 1) {
    currentPage = page;
    const search  = document.getElementById('filterSearch').value;
    const deptoId = document.getElementById('filterDepto').value;
    const sedeId  = document.getElementById('filterSede').value;
    let url = `/api/empleados?page=${page}&per_page=15`;
    if (search)  url += `&search=${encodeURIComponent(search)}`;
    if (deptoId) url += `&departamento_id=${deptoId}`;
    if (sedeId)  url += `&sede_id=${sedeId}`;

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
                    <td>${e.cedula || '—'}</td>
                    <td>${e.email || 'N/A'}</td>
                    <td>${rolBadge}</td>
                    <td>${e.departamento_id ? (deptoMap[e.departamento_id] || e.departamento_id) : '—'}</td>
                    <td>${e.cargo_id ? (cargoMap[e.cargo_id] || e.cargo_id) : '—'}</td>
                    <td>${(e.sede_ids && e.sede_ids.length) ? e.sede_ids.map(id => `<span class="badge bg-info text-dark me-1">${sedeMap[id] || id}</span>`).join('') : '—'}</td>
                    <td><span class="badge ${e.is_active ? 'bg-success' : 'bg-danger'}">${e.is_active ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <button class="btn btn-sm ${e.face_descriptor ? 'btn-success' : 'btn-outline-secondary'}" onclick="verRostros(${e.id}, '${(e.name||'').replace(/'/g,'')}')">
                            <i class="fa-solid fa-face-smile"></i>
                            <span class="ms-1">${e.face_descriptor ? '✓' : '—'}</span>
                        </button>
                    </td>
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

async function editEmpleado(id) {
    try {
        const res  = await fetch(`/api/empleados/${id}`, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const e = data.data;
        document.getElementById('empleadoId').value      = e.id;

        if (isAdminTenant) {
            const selEmp = document.getElementById('empEmpresaId');
            selEmp.value = e.empresa_id || '';
            if (e.tiene_movimientos) {
                selEmp.disabled = true;
                selEmp.title = 'No se puede cambiar: el empleado tiene registros de asistencia';
            } else {
                selEmp.disabled = false;
                selEmp.title = '';
            }
            // Cargar sedes de la empresa del empleado con las ya asignadas marcadas
            await loadSedesParaEmpresa(e.empresa_id, e.sede_ids || []);
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
        document.getElementById('empCodigo').value        = e.codigo_empleado;
        document.getElementById('empCodigoRow').style.display = '';
        document.getElementById('empCedula').value       = e.cedula || '';
        document.getElementById('empTelefono').value     = e.telefono || '';
        document.getElementById('empDepartamento').value = e.departamento_id || '';
        document.getElementById('empCargo').value        = e.cargo_id || '';
        document.getElementById('empHorario').value      = e.horario_id || '';
        // Para admin no-tenant, marcar las sedes ya asignadas
        if (!isAdminTenant) {
            document.querySelectorAll('.emp-sede-check').forEach(cb => {
                cb.checked = (e.sede_ids || []).includes(parseInt(cb.value));
            });
        }
        document.getElementById('empRole').value         = e.role || 'empleado';
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
    const payload = {
        name:             document.getElementById('empName').value,
        email:            document.getElementById('empEmail').value,
        cedula:           document.getElementById('empCedula').value,
        telefono:         document.getElementById('empTelefono').value,
        departamento_id:  deptoVal   ? parseInt(deptoVal)   : null,
        cargo_id:         cargoVal   ? parseInt(cargoVal)   : null,
        horario_id:       horarioVal ? parseInt(horarioVal) : null,
        sede_ids:         getSelectedSedeIds(),
        role:             document.getElementById('empRole').value,
        is_active:        document.getElementById('empActivo').checked,
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
        const res  = await fetch(`/api/empleados/${rostrosEmpleadoId}/imagenes-rostro?con_imagen=true`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
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
        const res = await fetch(`/api/empleados/${rostrosEmpleadoId}/imagenes-rostro/${imageId}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}` }
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

document.addEventListener('DOMContentLoaded', () => { loadCatalogos().then(() => loadEmpleados()); });
</script>
@endpush
