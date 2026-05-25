@extends('layouts.admin')
@section('title', 'Horarios')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-clock me-2 text-primary"></i>Horarios</h4>
                <p class="text-muted mb-0">Turnos y jornadas laborales</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fa-solid fa-plus me-1"></i> Nuevo Horario
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Desc. almuerzo</th>
                        <th>Horas laborables</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="horariosTbody">
                    <tr><td colspan="7" class="text-center text-muted py-3">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="horarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="horarioId">
                <div class="mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="hNombre" class="form-control" placeholder="Ej: Turno Mañana">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Hora Entrada <span class="text-danger">*</span></label>
                        <input type="time" id="hEntrada" class="form-control" step="60">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Hora Salida <span class="text-danger">*</span></label>
                        <input type="time" id="hSalida" class="form-control" step="60">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Minutos de almuerzo a descontar <small class="text-muted">(dejar vacío si no aplica)</small></label>
                    <input type="number" id="hDuracion" class="form-control" min="0" max="240" placeholder="Ej: 60">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="hActivo" checked>
                    <label class="form-check-label">Activo</label>
                </div>
                <div id="horarioError" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveHorario()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');

async function loadHorarios() {
    const res  = await fetch('/api/horarios', { headers: { 'Authorization': `Bearer ${token}` } });
    const data = await res.json();
    const tbody = document.getElementById('horariosTbody');
    if (!data.data?.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin horarios</td></tr>';
        return;
    }
    tbody.innerHTML = data.data.map(h => {
        const almuerzo = h.duracion_almuerzo_min ? `${h.duracion_almuerzo_min} min` : '—';

        // Calcular horas laborables = (salida - entrada) - descuento almuerzo
        let horasLabel = '—';
        if (h.hora_entrada && h.hora_salida) {
            const [hE, mE] = h.hora_entrada.split(':').map(Number);
            const [hS, mS] = h.hora_salida.split(':').map(Number);
            let minutos = (hS * 60 + mS) - (hE * 60 + mE);
            if (minutos < 0) minutos += 1440; // turno nocturno cruza medianoche
            minutos -= (h.duracion_almuerzo_min || 0);
            const hh = Math.floor(minutos / 60);
            const mm = String(minutos % 60).padStart(2, '0');
            horasLabel = `<strong>${hh}h ${mm}m</strong>`;
        }
        return `<tr>
            <td><strong>${h.nombre}</strong></td>
            <td>${h.hora_entrada?.slice(0,5) ?? '—'}</td>
            <td>${h.hora_salida?.slice(0,5) ?? '—'}</td>
            <td>${almuerzo}</td>
            <td>${horasLabel}</td>
            <td><span class="badge ${h.is_active ? 'bg-success' : 'bg-secondary'}">${h.is_active ? 'Activo' : 'Inactivo'}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick='editHorario(${JSON.stringify(h)})'><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteHorario(${h.id})"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>`;
    }).join('');
}

function openModal(data = null) {
    document.getElementById('horarioId').value = data?.id || '';
    document.getElementById('hNombre').value   = data?.nombre || '';
    document.getElementById('hEntrada').value  = data?.hora_entrada?.slice(0,5) || '';
    document.getElementById('hSalida').value   = data?.hora_salida?.slice(0,5) || '';
    document.getElementById('hDuracion').value = data?.duracion_almuerzo_min || '';
    document.getElementById('hActivo').checked = data ? data.is_active : true;
    document.getElementById('modalTitle').textContent = data ? 'Editar Horario' : 'Nuevo Horario';
    document.getElementById('horarioError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('horarioModal')).show();
}

function editHorario(h) { openModal(h); }

async function saveHorario() {
    const id = document.getElementById('horarioId').value;
    const nombre = document.getElementById('hNombre').value.trim();
    if (!nombre) { showError('horarioError', 'El nombre es obligatorio.'); return; }

    const toTime = v => v ? v + ':00' : null;
    const payload = {
        nombre,
        hora_entrada:          toTime(document.getElementById('hEntrada').value),
        hora_salida:           toTime(document.getElementById('hSalida').value),
        duracion_almuerzo_min: parseInt(document.getElementById('hDuracion').value) || null,
        is_active:             document.getElementById('hActivo').checked,
    };
    const url    = id ? `/api/horarios/${id}` : '/api/horarios';
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method, headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify(payload),
    });
    if (res.ok) {
        bootstrap.Modal.getInstance(document.getElementById('horarioModal')).hide();
        loadHorarios();
    } else {
        const err = await res.json();
        showError('horarioError', Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
    }
}

async function deleteHorario(id) {
    if (!confirm('¿Desactivar este horario?')) return;
    await fetch(`/api/horarios/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
    loadHorarios();
}

function showError(elId, msg) {
    const el = document.getElementById(elId);
    el.textContent = msg; el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', loadHorarios);
</script>
@endpush
