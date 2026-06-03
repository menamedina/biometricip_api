@extends('layouts.admin')
@section('title', 'Visitantes')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-user-check me-2 text-primary"></i>Visitantes</h4>
                    <p class="text-muted mb-0">Registro de visitas por sede</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label form-label-sm mb-1">Sede</label>
                            <select class="form-select form-select-sm" id="filterSede" onchange="loadVisitantes()">
                                <option value="">Todas las sedes</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Desde</label>
                            <input type="date" class="form-control form-control-sm" id="filterDesde" onchange="loadVisitantes()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Hasta</label>
                            <input type="date" class="form-control form-control-sm" id="filterHasta" onchange="loadVisitantes()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm mb-1">Cédula / Nombre</label>
                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Buscar..." oninput="loadVisitantes()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label form-label-sm mb-1">Estado</label>
                            <select class="form-select form-select-sm" id="filterEstado" onchange="loadVisitantes()">
                                <option value="">Todos</option>
                                <option value="en_sede">En sede</option>
                                <option value="salieron">Con salida</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-secondary w-100 mt-3" onclick="clearFilters()">
                                <i class="ti ti-x me-1"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Visita a</th>
                                    <th>Sede</th>
                                    <th>EPS / ARL</th>
                                    <th>Teléfono</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Tiempo en sede</th>
                                    <th>Foto</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="visitantesTbody">
                                <tr><td colspan="9" class="text-center text-muted py-3">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal foto --}}
<div class="modal fade" id="fotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Foto del visitante</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div id="fotoSpinner" class="py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <img id="fotoModalImg" src="" alt="Foto" class="rounded d-none" style="width:400px;height:400px;object-fit:cover;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');

// Fecha por defecto: hoy
document.getElementById('filterDesde').value = new Date().toISOString().slice(0,10);
document.getElementById('filterHasta').value = new Date().toISOString().slice(0,10);

async function loadSedes() {
    const res  = await fetch('/api/sedes', { headers: { Authorization: `Bearer ${token}` } });
    const data = await res.json();
    const sel  = document.getElementById('filterSede');
    (data.data || []).forEach(s => {
        const o = document.createElement('option');
        o.value = s.id; o.textContent = s.nombre;
        sel.appendChild(o);
    });
}

async function loadVisitantes() {
    const params = new URLSearchParams({
        sede_id:  document.getElementById('filterSede').value,
        desde:    document.getElementById('filterDesde').value,
        hasta:    document.getElementById('filterHasta').value,
        search:   document.getElementById('filterSearch').value,
        estado:   document.getElementById('filterEstado').value,
    });

    const res  = await fetch(`/api/visitantes?${params}`, { headers: { Authorization: `Bearer ${token}` } });
    const data = await res.json();
    const tbody = document.getElementById('visitantesTbody');

    if (!data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Sin registros</td></tr>';
        return;
    }

    tbody.innerHTML = data.data.map(v => `
        <tr>
            <td><strong>${v.nombre ?? '—'}</strong></td>
            <td>${v.cedula}</td>
            <td>${v.persona_visita ?? '—'}</td>
            <td><span class="badge bg-primary">${v.sede?.codigo ?? '—'}</span></td>
            <td><small>${v.eps ?? '—'} / ${v.arl ?? '—'}</small></td>
            <td>${v.telefono ?? '—'}</td>
            <td><small>${formatDT(v.hora_entrada)}</small></td>
            <td><small>${v.hora_salida ? formatDT(v.hora_salida) : '<span class="badge bg-warning text-dark">En sede</span>'}</small></td>
            <td><small>${tiempoEnSede(v.hora_entrada, v.hora_salida)}</small></td>
            <td>${v.imagen_entrada ? `<button class="btn btn-sm btn-outline-primary" onclick="verFoto(${v.id})"><i class="ti ti-photo"></i></button>` : '—'}</td>
            <td>${botonForzarSalida(v)}</td>
        </tr>
    `).join('');
}

function tiempoEnSede(entrada, salida) {
    if (!entrada) return '—';
    const inicio = new Date(entrada);
    const fin    = salida ? new Date(salida) : new Date();
    const mins   = Math.floor((fin - inicio) / 60000);
    if (mins < 0) return '—';
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    const texto = h > 0 ? `${h}h ${m}m` : `${m}m`;
    return salida
        ? texto
        : `<span class="text-warning fw-semibold">${texto} ⏳</span>`;
}

function botonForzarSalida(v) {
    if (v.hora_salida) return '';
    const horas   = (new Date() - new Date(v.hora_entrada)) / 3600000;
    const habilitado = horas >= 24;
    const title   = habilitado ? 'Registrar salida forzada' : 'Se habilita tras 24h sin salida';
    return `<button class="btn btn-sm btn-outline-danger" onclick="forzarSalida(${v.id})"
        ${habilitado ? '' : 'disabled'} title="${title}">
        <i class="ti ti-door-exit"></i>
    </button>`;
}

async function forzarSalida(id) {
    if (!confirm('¿Registrar salida forzada para este visitante?')) return;
    await fetch(`/api/visitantes/${id}/forzar-salida`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` },
    });
    loadVisitantes();
}

function formatDT(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('es-CO') + ' ' + d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

async function verFoto(visitanteId) {
    const img     = document.getElementById('fotoModalImg');
    const spinner = document.getElementById('fotoSpinner');
    img.classList.add('d-none');
    img.src = '';
    spinner.classList.remove('d-none');
    new bootstrap.Modal(document.getElementById('fotoModal')).show();
    const res  = await fetch(`/api/visitantes/${visitanteId}/foto`, { headers: { Authorization: `Bearer ${token}` } });
    const data = await res.json();
    img.src = data.foto ?? '';
    spinner.classList.add('d-none');
    img.classList.remove('d-none');
}

function clearFilters() {
    document.getElementById('filterSede').value   = '';
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterDesde').value  = new Date().toISOString().slice(0,10);
    document.getElementById('filterHasta').value  = new Date().toISOString().slice(0,10);
    loadVisitantes();
}

document.addEventListener('DOMContentLoaded', () => { loadSedes(); loadVisitantes(); });
</script>
@endpush
