@extends('layouts.admin')
@section('title', 'Permisos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-file-signature me-2 text-primary"></i>Permisos</h4>
                <p class="text-muted mb-0">Gestión de permisos de empleados</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Permiso
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="date" id="filterFrom" class="form-control form-control-sm" onchange="loadPermisos()">
                </div>
                <div class="col-md-2">
                    <input type="date" id="filterTo" class="form-control form-control-sm" onchange="loadPermisos()">
                </div>
                <div class="col-md-3">
                    <select id="filterEmpleado" class="form-select form-select-sm" onchange="loadPermisos()">
                        <option value="">Todos los empleados</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterEstado" class="form-select form-select-sm" onchange="loadPermisos()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Empleado</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Horas</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="permisosTbody">
                    <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div id="permisosPagination"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="permisoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Permiso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="permisoId">
                <div class="mb-3">
                    <label class="form-label">Empleado <span class="text-danger">*</span></label>
                    <select id="pEmpleado" class="form-select"></select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="pFecha" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select id="pTipo" class="form-select">
                            <option value="salida_temprana">Salida Temprana</option>
                            <option value="llegada_tarde">Llegada Tarde</option>
                            <option value="dia_completo">Día Completo</option>
                            <option value="horas">Horas</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Horas de permiso <span class="text-danger">*</span></label>
                    <input type="number" id="pHoras" class="form-control" min="0" max="24" step="0.5" placeholder="Ej: 2">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo</label>
                    <textarea id="pMotivo" class="form-control" rows="2" placeholder="Descripción del permiso..."></textarea>
                </div>
                <div id="permisoError" class="alert alert-danger py-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="savePermiso()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
const tipoLabels = {
    salida_temprana: 'Salida Temprana',
    llegada_tarde:   'Llegada Tarde',
    dia_completo:    'Día Completo',
    horas:           'Horas',
};
const estadoBadge = {
    pendiente: 'bg-warning text-dark',
    aprobado:  'bg-success',
    rechazado: 'bg-danger',
};

document.addEventListener('DOMContentLoaded', () => {
    const hoy   = new Date().toISOString().slice(0,10);
    const inicio = new Date(); inicio.setDate(1);
    document.getElementById('filterFrom').value = inicio.toISOString().slice(0,10);
    document.getElementById('filterTo').value   = hoy;
    loadEmpleados();
    loadPermisos();
});

async function loadEmpleados() {
    const res  = await fetch('/api/empleados?per_page=500', { headers: { 'Authorization': `Bearer ${token}` } });
    const data = await res.json();
    const empleados = data.data || [];
    const sel = document.getElementById('filterEmpleado');
    const pSel = document.getElementById('pEmpleado');
    pSel.innerHTML = '<option value="">— Seleccionar —</option>';
    empleados.forEach(e => {
        const opt = `<option value="${e.id}">${e.name} (${e.codigo_empleado})</option>`;
        sel.innerHTML += opt;
        pSel.innerHTML += opt;
    });
}

async function loadPermisos(page = 1) {
    const from    = document.getElementById('filterFrom').value;
    const to      = document.getElementById('filterTo').value;
    const userId  = document.getElementById('filterEmpleado').value;
    const estado  = document.getElementById('filterEstado').value;
    let url = `/api/permisos?page=${page}&per_page=20`;
    if (from)   url += `&date_from=${from}`;
    if (to)     url += `&date_to=${to}`;
    if (userId) url += `&user_id=${userId}`;
    if (estado) url += `&estado=${estado}`;

    const res  = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
    const data = await res.json();
    const tbody = document.getElementById('permisosTbody');
    const items = data.data || [];

    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin permisos</td></tr>';
        return;
    }
    tbody.innerHTML = items.map(p => `<tr>
        <td><strong>${p.user?.name ?? 'N/A'}</strong><br><small class="text-muted">${p.user?.codigo_empleado ?? ''}</small></td>
        <td>${p.fecha}</td>
        <td>${tipoLabels[p.tipo] ?? p.tipo}</td>
        <td>${p.horas_permiso}h</td>
        <td><small class="text-muted">${p.motivo || '—'}</small></td>
        <td><span class="badge ${estadoBadge[p.estado] ?? 'bg-secondary'}">${p.estado}</span></td>
        <td>
            ${p.estado === 'pendiente' ? `
            <button class="btn btn-sm btn-success me-1" onclick="aprobar(${p.id})" title="Aprobar"><i class="fa-solid fa-check"></i></button>
            <button class="btn btn-sm btn-danger me-1" onclick="rechazar(${p.id})" title="Rechazar"><i class="fa-solid fa-xmark"></i></button>
            ` : ''}
            <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${p.id})"><i class="fa-solid fa-trash"></i></button>
        </td>
    </tr>`).join('');

    // Paginación
    const nav = document.getElementById('permisosPagination');
    if (data.last_page > 1) {
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= data.last_page; i++) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="loadPermisos(${i});return false">${i}</a></li>`;
        }
        nav.innerHTML = html + '</ul></nav>';
    } else nav.innerHTML = '';
}

function openModal() {
    document.getElementById('permisoId').value = '';
    document.getElementById('pEmpleado').value = '';
    document.getElementById('pFecha').value    = new Date().toISOString().slice(0,10);
    document.getElementById('pTipo').value     = 'salida_temprana';
    document.getElementById('pHoras').value   = '';
    document.getElementById('pMotivo').value   = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Permiso';
    document.getElementById('permisoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('permisoModal')).show();
}

async function savePermiso() {
    const userId = document.getElementById('pEmpleado').value;
    const fecha  = document.getElementById('pFecha').value;
    const horas  = document.getElementById('pHoras').value;
    if (!userId || !fecha || !horas) {
        const el = document.getElementById('permisoError');
        el.textContent = 'Empleado, fecha y horas son obligatorios.';
        el.style.display = 'block';
        return;
    }
    const payload = {
        user_id:       parseInt(userId),
        fecha,
        tipo:          document.getElementById('pTipo').value,
        horas_permiso: parseFloat(horas),
        motivo:        document.getElementById('pMotivo').value,
    };
    const res = await fetch('/api/permisos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify(payload),
    });
    if (res.ok) {
        bootstrap.Modal.getInstance(document.getElementById('permisoModal')).hide();
        loadPermisos();
    } else {
        const err = await res.json();
        const el = document.getElementById('permisoError');
        el.textContent = Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error';
        el.style.display = 'block';
    }
}

async function aprobar(id) {
    if (!confirm('¿Aprobar este permiso?')) return;
    await fetch(`/api/permisos/${id}/aprobar`, { method: 'POST', headers: { 'Authorization': `Bearer ${token}` } });
    loadPermisos();
}

async function rechazar(id) {
    if (!confirm('¿Rechazar este permiso?')) return;
    await fetch(`/api/permisos/${id}/rechazar`, { method: 'POST', headers: { 'Authorization': `Bearer ${token}` } });
    loadPermisos();
}

async function eliminar(id) {
    if (!confirm('¿Eliminar este permiso?')) return;
    await fetch(`/api/permisos/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
    loadPermisos();
}
</script>
@endpush
