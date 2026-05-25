@extends('layouts.admin')
@section('title', 'Festivos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-calendar-xmark me-2 text-primary"></i>Festivos</h4>
                <p class="text-muted mb-0">Días no laborables</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Festivo
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-2">
            <select id="filterYear" class="form-select form-select-sm w-auto" onchange="loadFestivos()">
                <option value="">Todos los años</option>
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody id="festivosTbody">
                    <tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="festivoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Festivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="festivoId">
                <div class="mb-3">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" id="fFecha" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="fNombre" class="form-control" placeholder="Ej: Día de la Independencia">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="fActivo" checked>
                    <label class="form-check-label">Activo</label>
                </div>
                <div id="festivoError" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveFestivo()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');

// Llenar select de años
const currentYear = new Date().getFullYear();
const selYear = document.getElementById('filterYear');
for (let y = currentYear + 1; y >= currentYear - 2; y--) {
    const opt = document.createElement('option');
    opt.value = y; opt.textContent = y;
    if (y === currentYear) opt.selected = true;
    selYear.appendChild(opt);
}

async function loadFestivos() {
    const year = document.getElementById('filterYear').value;
    let url = '/api/festivos';
    if (year) url += `?year=${year}`;
    const res  = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
    const data = await res.json();
    const tbody = document.getElementById('festivosTbody');
    if (!data.data?.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin festivos</td></tr>';
        return;
    }
    tbody.innerHTML = data.data.map(f => `<tr>
        <td><strong>${f.fecha.slice(0,10).split('-').reverse().join('/')}</strong></td>
        <td>${f.nombre}</td>
        <td><span class="badge ${f.is_active ? 'bg-success' : 'bg-secondary'}">${f.is_active ? 'Activo' : 'Inactivo'}</span></td>
        <td>
            <button class="btn btn-sm btn-outline-primary me-1" onclick='editFestivo(${JSON.stringify(f)})'><i class="fa-solid fa-pen"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteFestivo(${f.id})"><i class="fa-solid fa-trash"></i></button>
        </td>
    </tr>`).join('');
}

function openModal(data = null) {
    document.getElementById('festivoId').value  = data?.id || '';
    document.getElementById('fFecha').value     = data?.fecha || '';
    document.getElementById('fNombre').value    = data?.nombre || '';
    document.getElementById('fActivo').checked  = data ? data.is_active : true;
    document.getElementById('modalTitle').textContent = data ? 'Editar Festivo' : 'Nuevo Festivo';
    document.getElementById('festivoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('festivoModal')).show();
}

function editFestivo(f) { openModal(f); }

async function saveFestivo() {
    const id = document.getElementById('festivoId').value;
    const fecha  = document.getElementById('fFecha').value;
    const nombre = document.getElementById('fNombre').value.trim();
    if (!fecha || !nombre) { showError('festivoError', 'Fecha y nombre son obligatorios.'); return; }

    const payload = { fecha, nombre, is_active: document.getElementById('fActivo').checked };
    const url    = id ? `/api/festivos/${id}` : '/api/festivos';
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify(payload),
    });
    if (res.ok) {
        bootstrap.Modal.getInstance(document.getElementById('festivoModal')).hide();
        loadFestivos();
    } else {
        const err = await res.json();
        showError('festivoError', Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
    }
}

async function deleteFestivo(id) {
    if (!confirm('¿Eliminar este festivo?')) return;
    await fetch(`/api/festivos/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
    loadFestivos();
}

function showError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg; el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', loadFestivos);
</script>
@endpush
