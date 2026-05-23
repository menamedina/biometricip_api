@extends('layouts.admin')
@section('title', 'Sedes')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-building me-2 text-primary"></i>Sedes</h4>
                    <p class="text-muted mb-0">Gestión de oficinas y geocercas</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sedeModal" onclick="resetForm()">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Sede
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Coordenadas</th>
                                    <th>Radio (mts)</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="sedesTbody">
                                <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sedeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sedeModalTitle">Nueva Sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="sedeForm">
                    <input type="hidden" id="sedeId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" id="sedeCodigo" class="form-control" placeholder="SEDE-001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="sedeNombre" class="form-control" placeholder="Sede Principal" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" id="sedeDireccion" class="form-control" placeholder="Av. Reforma 222, CDMX">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitud</label>
                            <input type="number" step="0.0000001" id="sedeLat" class="form-control" placeholder="19.4326" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitud</label>
                            <input type="number" step="0.0000001" id="sedeLng" class="form-control" placeholder="-99.1332" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Radio (metros)</label>
                            <input type="number" id="sedeRadio" class="form-control" value="150" min="10" max="5000">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sedeActivo" checked>
                                <label class="form-check-label">Activo</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveSede()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');

function resetForm() {
    document.getElementById('sedeForm').reset();
    document.getElementById('sedeId').value = '';
    document.getElementById('sedeModalTitle').textContent = 'Nueva Sede';
    document.getElementById('sedeActivo').checked = true;
}

async function loadSedes() {
    try {
        const res = await fetch('/api/sedes', { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const tbody = document.getElementById('sedesTbody');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin sedes registradas</td></tr>';
            return;
        }
        tbody.innerHTML = data.data.map(s => `
            <tr>
                <td><span class="badge bg-primary">${s.codigo}</span></td>
                <td><strong>${s.nombre}</strong></td>
                <td>${s.direccion || '—'}</td>
                <td><small>${Number(s.lat).toFixed(4)}, ${Number(s.lng).toFixed(4)}</small></td>
                <td>${s.radio_mts}m</td>
                <td><span class="badge ${s.is_active ? 'bg-success' : 'bg-danger'}">${s.is_active ? 'Activo' : 'Inactivo'}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick='editSede(${JSON.stringify(s).replace(/'/g, "&#39;")})'><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSede(${s.id})"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
    } catch(e) { console.error(e); }
}

function editSede(sede) {
    document.getElementById('sedeId').value = sede.id;
    document.getElementById('sedeCodigo').value = sede.codigo;
    document.getElementById('sedeNombre').value = sede.nombre;
    document.getElementById('sedeDireccion').value = sede.direccion || '';
    document.getElementById('sedeLat').value = sede.lat;
    document.getElementById('sedeLng').value = sede.lng;
    document.getElementById('sedeRadio').value = sede.radio_mts;
    document.getElementById('sedeActivo').checked = sede.is_active;
    document.getElementById('sedeModalTitle').textContent = 'Editar Sede';
    new bootstrap.Modal(document.getElementById('sedeModal')).show();
}

async function saveSede() {
    const id = document.getElementById('sedeId').value;
    const payload = {
        codigo: document.getElementById('sedeCodigo').value,
        nombre: document.getElementById('sedeNombre').value,
        direccion: document.getElementById('sedeDireccion').value,
        lat: parseFloat(document.getElementById('sedeLat').value),
        lng: parseFloat(document.getElementById('sedeLng').value),
        radio_mts: parseInt(document.getElementById('sedeRadio').value) || 150,
        is_active: document.getElementById('sedeActivo').checked,
    };

    const url = id ? `/api/sedes/${id}` : '/api/sedes';
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method, headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('sedeModal')).hide();
            loadSedes();
        } else {
            const err = await res.json();
            alert(err.message || 'Error al guardar');
        }
    } catch(e) { console.error(e); }
}

async function deleteSede(id) {
    if (!confirm('¿Eliminar esta sede?')) return;
    try {
        await fetch(`/api/sedes/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
        loadSedes();
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', loadSedes);
</script>
@endpush
