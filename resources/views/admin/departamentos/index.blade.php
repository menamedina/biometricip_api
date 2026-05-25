@extends('layouts.admin')
@section('title', 'Departamentos y Cargos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-sitemap me-2 text-primary"></i>Departamentos y Cargos</h4>
                    <p class="text-muted mb-0">Estructura organizacional de la empresa</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Departamentos --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-building-columns me-1"></i> Departamentos</h5>
                    <button class="btn btn-sm btn-primary" onclick="openDeptoModal()">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="deptosTbody">
                            <tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cargos --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-user-tie me-1"></i> Cargos</h5>
                    <button class="btn btn-sm btn-primary" onclick="openCargoModal()">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="cargosTbody">
                            <tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Departamento --}}
<div class="modal fade" id="deptoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptoModalTitle">Nuevo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deptoId">
                <div class="mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="deptoNombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" id="deptoDescripcion" class="form-control">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="deptoActivo" checked>
                    <label class="form-check-label">Activo</label>
                </div>
                <div id="deptoError" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveDepto()">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cargo --}}
<div class="modal fade" id="cargoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cargoModalTitle">Nuevo Cargo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cargoId">
                <div class="mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="cargoNombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" id="cargoDescripcion" class="form-control">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="cargoActivo" checked>
                    <label class="form-check-label">Activo</label>
                </div>
                <div id="cargoError" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveCargo()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');

// ── Departamentos ─────────────────────────────────────────────────────────────
async function loadDeptos() {
    const res  = await fetch('/api/departamentos', { headers: { 'Authorization': `Bearer ${token}` } });
    const data = await res.json();
    const tbody = document.getElementById('deptosTbody');
    if (!data.data?.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin departamentos</td></tr>';
        return;
    }
    tbody.innerHTML = data.data.map(d => `
        <tr>
            <td><strong>${d.nombre}</strong></td>
            <td><small class="text-muted">${d.descripcion || '—'}</small></td>
            <td><span class="badge ${d.is_active ? 'bg-success' : 'bg-secondary'}">${d.is_active ? 'Activo' : 'Inactivo'}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick='editDepto(${JSON.stringify(d)})'><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteDepto(${d.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>
    `).join('');

}

function openDeptoModal(data = null) {
    document.getElementById('deptoId').value        = data?.id || '';
    document.getElementById('deptoNombre').value    = data?.nombre || '';
    document.getElementById('deptoDescripcion').value = data?.descripcion || '';
    document.getElementById('deptoActivo').checked  = data ? data.is_active : true;
    document.getElementById('deptoModalTitle').textContent = data ? 'Editar Departamento' : 'Nuevo Departamento';
    document.getElementById('deptoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('deptoModal')).show();
}

function editDepto(d) { openDeptoModal(d); }

async function saveDepto() {
    const id      = document.getElementById('deptoId').value;
    const nombre  = document.getElementById('deptoNombre').value.trim();
    if (!nombre) { showError('deptoError', 'El nombre es obligatorio.'); return; }

    const payload = {
        nombre,
        descripcion: document.getElementById('deptoDescripcion').value,
        is_active:   document.getElementById('deptoActivo').checked,
    };
    const url    = id ? `/api/departamentos/${id}` : '/api/departamentos';
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify(payload),
    });
    if (res.ok) {
        bootstrap.Modal.getInstance(document.getElementById('deptoModal')).hide();
        loadDeptos(); loadCargos();
    } else {
        const err = await res.json();
        showError('deptoError', Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
    }
}

async function deleteDepto(id) {
    if (!confirm('¿Eliminar este departamento? Los cargos asociados quedarán sin departamento.')) return;
    await fetch(`/api/departamentos/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
    loadDeptos(); loadCargos();
}

// ── Cargos ────────────────────────────────────────────────────────────────────
async function loadCargos() {
    const res  = await fetch('/api/cargos', { headers: { 'Authorization': `Bearer ${token}` } });
    const data = await res.json();
    const tbody = document.getElementById('cargosTbody');
    if (!data.data?.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin cargos</td></tr>';
        return;
    }
    tbody.innerHTML = data.data.map(c => `
        <tr>
            <td><strong>${c.nombre}</strong></td>
            <td><small class="text-muted">${c.descripcion || '—'}</small></td>
            <td><span class="badge ${c.is_active ? 'bg-success' : 'bg-secondary'}">${c.is_active ? 'Activo' : 'Inactivo'}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick='editCargo(${JSON.stringify(c)})'><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteCargo(${c.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

function openCargoModal(data = null) {
    document.getElementById('cargoId').value          = data?.id || '';
    document.getElementById('cargoNombre').value      = data?.nombre || '';
    document.getElementById('cargoDescripcion').value = data?.descripcion || '';
    document.getElementById('cargoActivo').checked    = data ? data.is_active : true;
    document.getElementById('cargoModalTitle').textContent = data ? 'Editar Cargo' : 'Nuevo Cargo';
    document.getElementById('cargoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('cargoModal')).show();
}

function editCargo(c) { openCargoModal(c); }

async function saveCargo() {
    const id     = document.getElementById('cargoId').value;
    const nombre = document.getElementById('cargoNombre').value.trim();
    if (!nombre) { showError('cargoError', 'El nombre es obligatorio.'); return; }

    const payload = {
        nombre,
        descripcion: document.getElementById('cargoDescripcion').value,
        is_active:   document.getElementById('cargoActivo').checked,
    };
    const url    = id ? `/api/cargos/${id}` : '/api/cargos';
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify(payload),
    });
    if (res.ok) {
        bootstrap.Modal.getInstance(document.getElementById('cargoModal')).hide();
        loadCargos();
    } else {
        const err = await res.json();
        showError('cargoError', Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
    }
}

async function deleteCargo(id) {
    if (!confirm('¿Eliminar este cargo?')) return;
    await fetch(`/api/cargos/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
    loadCargos();
}

function showError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg;
    el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => { loadDeptos(); loadCargos(); });
</script>
@endpush
