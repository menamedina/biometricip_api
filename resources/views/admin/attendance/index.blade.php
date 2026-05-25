@extends('layouts.admin')
@section('title', 'Registros de Asistencia')

@section('content')
<!-- Modal Foto -->
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-camera me-2"></i>Foto de evidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="modalFotoBody">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <h4 class="mb-1"><i class="fa-solid fa-clock me-2 text-primary"></i>Registros de Asistencia</h4>
            <p class="text-muted mb-0">Historial completo de entradas y salidas</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-2">
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small">Fecha</label>
                            <input type="date" class="form-control form-control-sm" id="filterDate" value="{{ date('Y-m-d') }}" onchange="loadRecords()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tipo</label>
                            <select class="form-select form-select-sm" id="filterTipo" onchange="loadRecords()">
                                <option value="">Todos</option>
                                <option value="entrada">Entrada</option>
                                <option value="salida_almuerzo">Salida Almuerzo</option>
                                <option value="regreso_almuerzo">Regreso Almuerzo</option>
                                <option value="salida">Salida</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Empleado</label>
                            <select class="form-select form-select-sm" id="filterEmpleado" onchange="loadRecords()">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <div>
                                    <label class="form-label small">Desde</label>
                                    <input type="date" class="form-control form-control-sm" id="reportFrom" value="{{ date('Y-m-01') }}">
                                </div>
                                <div>
                                    <label class="form-label small">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" id="reportTo" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="d-flex align-items-end">
                                    <button class="btn btn-sm btn-success" onclick="exportCSV()"><i class="fa-solid fa-file-csv me-1"></i> Exportar CSV</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Código</th>
                                <th>Sede</th>
                                <th>Tipo</th>
                                <th>Fecha/Hora</th>
                                <th>Método</th>
                                <th>Foto</th>
                                <th>QR</th>
                                <th>Geocerca</th>
                                <th>Distancia</th>
                            </tr>
                        </thead>
                        <tbody id="recordsTbody">
                            <tr><td colspan="10" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="recordsInfo"></small>
                    <div id="recordsPagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
let recordsPage = 1;

async function loadRecords(page = 1) {
    recordsPage = page;
    const date = document.getElementById('filterDate').value;
    const tipo = document.getElementById('filterTipo').value;
    const empId = document.getElementById('filterEmpleado').value;
    let url = `/api/attendance?page=${page}&per_page=20`;
    if (date) url += `&date=${date}`;
    if (tipo) url += `&tipo=${tipo}`;
    if (empId) url += `&user_id=${empId}`;

    try {
        const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const tbody = document.getElementById('recordsTbody');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Sin registros</td></tr>';
        } else {
            tbody.innerHTML = data.data.map(r => {
                const tienefoto = r.foto_evidencia === 'base64';
                const fotoHtml = tienefoto
                    ? `<button class="btn btn-sm btn-outline-primary" onclick="verFoto(${r.id})" title="Ver foto">
                         <i class="fa-solid fa-camera"></i>
                       </button>`
                    : '<span class="text-muted">—</span>';
                return `
                <tr>
                    <td><strong>${r.user?.name || 'N/A'}</strong></td>
                    <td><span class="badge bg-primary">${r.user?.codigo_empleado || '—'}</span></td>
                    <td>${r.sede?.nombre || '—'}</td>
                    <td><span class="badge ${r.tipo.includes('entrada') ? 'bg-success' : 'bg-danger'}">${r.tipo.replace(/_/g, ' ')}</span></td>
                    <td>${new Date(r.fecha_hora).toLocaleString('es-MX')}</td>
                    <td><span class="badge bg-info">${r.metodo}</span></td>
                    <td>${fotoHtml}</td>
                    <td><span class="badge ${r.qr_validado ? 'bg-success' : 'bg-danger'}">${r.qr_validado ? 'Sí' : 'No'}</span></td>
                    <td><span class="badge ${r.geocerca_validada ? 'bg-success' : 'bg-danger'}">${r.geocerca_validada ? 'Sí' : 'No'}</span></td>
                    <td>${r.distancia_oficina_mts ? r.distancia_oficina_mts + 'm' : '—'}</td>
                </tr>`;
            }).join('');
        }
        document.getElementById('recordsInfo').textContent = `${data.total || 0} registros`;
        renderRecordsPagination(data);
    } catch(e) { console.error(e); }
}

function renderRecordsPagination(data) {
    const nav = document.getElementById('recordsPagination');
    if (!data.last_page || data.last_page <= 1) { nav.innerHTML = ''; return; }
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    for (let i = 1; i <= data.last_page; i++) {
        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="loadRecords(${i});return false">${i}</a></li>`;
    }
    html += '</ul></nav>';
    nav.innerHTML = html;
}

async function loadEmpleadosFilter() {
    try {
        const res = await fetch('/api/empleados?per_page=200', { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        const sel = document.getElementById('filterEmpleado');
        (data.data || []).forEach(e => {
            sel.innerHTML += `<option value="${e.id}">${e.name || ''} (${e.codigo_empleado})</option>`;
        });
    } catch(e) {}
}

function exportCSV() {
    const from = document.getElementById('reportFrom').value;
    const to = document.getElementById('reportTo').value;
    window.open(`/admin/reports/export?date_from=${from}&date_to=${to}`, '_blank');
}

async function verFoto(id) {
    const body = document.getElementById('modalFotoBody');
    body.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
    const modal = new bootstrap.Modal(document.getElementById('modalFoto'));
    modal.show();
    try {
        const res = await fetch(`/api/attendance/${id}/photo`, { headers: { 'Authorization': `Bearer ${token}` } });
        if (!res.ok) { body.innerHTML = '<p class="text-muted">Sin foto disponible</p>'; return; }
        const data = await res.json();
        body.innerHTML = `<img src="${data.foto_base64}" class="img-fluid rounded" style="max-height:500px;">`;
    } catch(e) {
        body.innerHTML = '<p class="text-danger">Error al cargar la foto</p>';
    }
}

document.addEventListener('DOMContentLoaded', () => { loadRecords(); loadEmpleadosFilter(); });
</script>
@endpush
