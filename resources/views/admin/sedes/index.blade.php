@extends('layouts.admin')
@section('title', 'Sedes')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
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
            @if(auth()->user()->admin_tenant ?? false)
            <div class="mt-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap"><i class="fa-solid fa-building-user me-1"></i>Empresa:</label>
                    <select id="filterEmpresaSedes" class="form-select form-select-sm w-auto" onchange="onFilterEmpresaChange()">
                        <option value="">— Selecciona una empresa —</option>
                    </select>
                </div>
            </div>
            @endif
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sedeModalTitle">Nueva Sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="sedeForm">
                    <input type="hidden" id="sedeId">
                    @if(auth()->user()->admin_tenant ?? false)
                    <div class="mb-3" id="sedeEmpresaRow">
                        <label class="form-label">Empresa <span class="text-danger">*</span></label>
                        <select id="sedeEmpresaId" class="form-select" required>
                            <option value="">— Selecciona una empresa —</option>
                        </select>
                    </div>
                    @endif
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
                            <input type="text" id="sedeDireccion" class="form-control" placeholder="Bogotá">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Latitud</label>
                            <input type="number" step="0.0000001" id="sedeLat" class="form-control" placeholder="19.4326" required oninput="syncMapFromInputs()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Longitud</label>
                            <input type="number" step="0.0000001" id="sedeLng" class="form-control" placeholder="-99.1332" required oninput="syncMapFromInputs()">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Radio (m)</label>
                            <input type="number" id="sedeRadio" class="form-control" value="150" min="10" max="5000" oninput="syncMapFromInputs()">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sedeActivo" checked>
                                <label class="form-check-label">Activo</label>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- Mapa -->
                <div class="mb-1">
                    <small class="text-muted"><i class="fa-solid fa-hand-pointer me-1"></i>Haz clic en el mapa para colocar la ubicación. Puedes arrastrar el marcador.</small>
                </div>
                <div id="sedeMap" style="height: 320px; border-radius: 10px; border: 1px solid #dee2e6;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveSede()">Guardar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal QR estático (imprimible) --}}
<div class="modal fade" id="staticQrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-print me-2"></i>QR Estático — <span id="staticQrSedeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="staticQrNotEnabled" class="text-center py-4 d-none">
                    <p class="text-muted">Esta sede no tiene QR estático habilitado.</p>
                    <button class="btn btn-primary" onclick="enableStaticQR()">
                        <i class="fa-solid fa-toggle-on me-1"></i> Habilitar QR Estático
                    </button>
                </div>
                <div id="staticQrContent" class="d-none">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        Este QR es <strong>permanente</strong> — imprímelo y pégalo en la pared.
                        La seguridad depende de la <strong>geocerca</strong>. Si el QR se compromete, usa "Regenerar" para invalidar los impresos anteriores.
                    </div>
                    <div class="text-center py-2" id="staticQrCanvas"></div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button class="btn btn-primary" onclick="printStaticQR()">
                            <i class="fa-solid fa-print me-1"></i> Imprimir
                        </button>
                        <button class="btn btn-warning" onclick="regenerateStaticQR()">
                            <i class="fa-solid fa-rotate me-1"></i> Regenerar (invalida QRs anteriores)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal QR Web (v3) --}}
<div class="modal fade" id="webQrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-globe me-2"></i>QR Web (sin app) — <span id="webQrSedeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="webQrNotEnabled" class="text-center py-4 d-none">
                    <p class="text-muted">Esta sede no tiene QR Web habilitado.</p>
                    <button class="btn btn-primary" onclick="enableWebQR()">
                        <i class="fa-solid fa-toggle-on me-1"></i> Habilitar QR Web
                    </button>
                </div>
                <div id="webQrContent" class="d-none">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        Este QR abre una <strong>página web</strong> — el usuario ingresa su cédula y se toma una foto, <strong>sin necesitar la app</strong>.
                        Es fijo e imprimible. Usa "Regenerar" si se compromete.
                    </div>
                    <div class="text-center py-2" id="webQrCanvas"></div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button class="btn btn-primary" onclick="printWebQR()">
                            <i class="fa-solid fa-print me-1"></i> Imprimir
                        </button>
                        <button class="btn btn-warning" onclick="regenerateWebQR()">
                            <i class="fa-solid fa-rotate me-1"></i> Regenerar (invalida QRs anteriores)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal QR --}}
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-qrcode me-2"></i><span id="qrSedeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div id="qrCanvas" class="d-flex justify-content-center mb-3"></div>
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <span class="text-muted small">Expira en</span>
                    <span id="qrCountdown" class="badge bg-warning text-dark fs-6">30s</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const token = localStorage.getItem('token');
const isAdminTenant = {{ (auth()->user()->admin_tenant ?? false) ? 'true' : 'false' }};
let currentEmpresaId = null; // empresa seleccionada para admin_tenant

// ── Mapa Google Maps ──────────────────────────────────────────────────────────
let gMap = null, gMarker = null, gCircle = null;
const DEFAULT_LAT = 4.7110, DEFAULT_LNG = -74.0721;

function initMap(lat, lng, radio) {
    lat   = lat   || DEFAULT_LAT;
    lng   = lng   || DEFAULT_LNG;
    radio = radio || 150;

    const center = { lat, lng };

    if (gMap) {
        gMap = null; gMarker = null; gCircle = null;
        document.getElementById('sedeMap').innerHTML = '';
    }

    gMap = new google.maps.Map(document.getElementById('sedeMap'), {
        center,
        zoom: 6,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
    });

    gMarker = new google.maps.Marker({
        position: center,
        map: gMap,
        draggable: true,
        title: 'Sede',
    });

    gCircle = new google.maps.Circle({
        map: gMap,
        center,
        radius: radio,
        strokeColor: '#4F46E5',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#4F46E5',
        fillOpacity: 0.1,
    });

    gMarker.addListener('dragend', function () {
        const pos = gMarker.getPosition();
        setCoords(pos.lat(), pos.lng());
        gCircle.setCenter(pos);
    });

    gMap.addListener('click', function (e) {
        const pos = e.latLng;
        gMarker.setPosition(pos);
        gCircle.setCenter(pos);
        setCoords(pos.lat(), pos.lng());
    });
}

function setCoords(lat, lng) {
    document.getElementById('sedeLat').value = lat.toFixed(7);
    document.getElementById('sedeLng').value = lng.toFixed(7);
}

function syncMapFromInputs() {
    if (!gMap) return;
    const lat   = parseFloat(document.getElementById('sedeLat').value);
    const lng   = parseFloat(document.getElementById('sedeLng').value);
    const radio = parseInt(document.getElementById('sedeRadio').value) || 150;
    gCircle.setRadius(radio);
    if (!isNaN(lat) && !isNaN(lng)) {
        const pos = { lat, lng };
        gMarker.setPosition(pos);
        gCircle.setCenter(pos);
        gMap.setCenter(pos);
    }
}

let googleMapsLoaded = false;
let pendingMapInit = null;

function googleMapsReady() {
    googleMapsLoaded = true;
    if (pendingMapInit) {
        const { lat, lng, radio } = pendingMapInit;
        pendingMapInit = null;
        initMap(lat, lng, radio);
    }
}

document.getElementById('sedeModal').addEventListener('shown.bs.modal', function () {
    const lat   = parseFloat(document.getElementById('sedeLat').value) || DEFAULT_LAT;
    const lng   = parseFloat(document.getElementById('sedeLng').value) || DEFAULT_LNG;
    const radio = parseInt(document.getElementById('sedeRadio').value) || 150;
    if (googleMapsLoaded) {
        initMap(lat, lng, radio);
    } else {
        pendingMapInit = { lat, lng, radio };
    }
});
// ─────────────────────────────────────────────────────────────────────────────

function resetForm() {
    document.getElementById('sedeForm').reset();
    document.getElementById('sedeId').value = '';
    document.getElementById('sedeModalTitle').textContent = 'Nueva Sede';
    document.getElementById('sedeActivo').checked = true;
    document.getElementById('sedeRadio').value = 150;
    clearSedeError();
    if (isAdminTenant) {
        // Pre-selecciona la empresa del filtro activo
        const sel = document.getElementById('sedeEmpresaId');
        if (sel) sel.value = currentEmpresaId || '';
    }
}

function buildHeaders(extra = {}) {
    const h = { 'Authorization': `Bearer ${token}`, ...extra };
    if (isAdminTenant && currentEmpresaId) h['X-Empresa-Id'] = currentEmpresaId;
    return h;
}

function onFilterEmpresaChange() {
    const sel = document.getElementById('filterEmpresaSedes');
    currentEmpresaId = sel.value || null;
    loadSedes();
}

async function loadEmpresas() {
    if (!isAdminTenant) return;
    const res = await fetch('/api/empresas', { headers: { 'Authorization': `Bearer ${token}` } });
    if (!res.ok) return;
    const data = await res.json();
    const filterSel = document.getElementById('filterEmpresaSedes');
    const formSel   = document.getElementById('sedeEmpresaId');
    (data.data || []).forEach(e => {
        filterSel.innerHTML += `<option value="${e.id}">${e.nombre}</option>`;
        if (formSel) formSel.innerHTML += `<option value="${e.id}">${e.nombre}</option>`;
    });
}

async function loadSedes() {
    if (isAdminTenant && !currentEmpresaId) {
        document.getElementById('sedesTbody').innerHTML =
            '<tr><td colspan="7" class="text-center text-muted py-3">Selecciona una empresa para ver sus sedes.</td></tr>';
        return;
    }
    try {
        const res = await fetch('/api/sedes', { headers: buildHeaders() });
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
                    <button class="btn btn-sm btn-outline-success me-1" onclick="showQR(${s.id}, '${s.nombre}')" title="QR dinámico (kiosco)"><i class="fa-solid fa-qrcode"></i></button>
                    <button class="btn btn-sm ${s.qr_static_token ? 'btn-outline-primary' : 'btn-outline-secondary'} me-1" onclick="showStaticQR(${s.id}, '${s.nombre}', ${s.qr_static_token ? 'true' : 'false'})" title="${s.qr_static_token ? 'QR estático (imprimible)' : 'Habilitar QR estático'}"><i class="fa-solid fa-print"></i></button>
                    <button class="btn btn-sm ${s.qr_v3_token ? 'btn-outline-info' : 'btn-outline-secondary'} me-1" onclick="showWebQR(${s.id}, '${s.nombre}', ${s.qr_v3_token ? 'true' : 'false'})" title="${s.qr_v3_token ? 'QR Web (sin app)' : 'Habilitar QR Web'}"><i class="fa-solid fa-globe"></i></button>
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
    if (isAdminTenant) {
        const sel = document.getElementById('sedeEmpresaId');
        if (sel) sel.value = currentEmpresaId || '';
    }
    new bootstrap.Modal(document.getElementById('sedeModal')).show();
}

async function saveSede() {
    const id     = document.getElementById('sedeId').value;
    const codigo = document.getElementById('sedeCodigo').value.trim();

    const nombre = document.getElementById('sedeNombre').value.trim();
    const lat    = parseFloat(document.getElementById('sedeLat').value);
    const lng    = parseFloat(document.getElementById('sedeLng').value);

    if (isAdminTenant) {
        const empSel = document.getElementById('sedeEmpresaId');
        if (!empSel || !empSel.value) { showSedeError('Debes seleccionar una empresa.'); return; }
        currentEmpresaId = empSel.value;
    }

    if (!codigo) { showSedeError('El código de la sede es obligatorio.'); return; }
    if (!nombre)  { showSedeError('El nombre de la sede es obligatorio.'); return; }
    if (isNaN(lat) || isNaN(lng)) {
        showSedeError('Debes hacer clic en el mapa o ingresar latitud y longitud manualmente.');
        return;
    }

    const payload = {
        codigo,
        nombre,
        direccion: document.getElementById('sedeDireccion').value,
        lat,
        lng,
        radio_mts: parseInt(document.getElementById('sedeRadio').value) || 150,
        is_active: document.getElementById('sedeActivo').checked,
    };

    const url    = id ? `/api/sedes/${id}` : '/api/sedes';
    const method = id ? 'PUT' : 'POST';

    clearSedeError();
    try {
        const res = await fetch(url, {
            method, headers: buildHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('sedeModal')).hide();
            loadSedes();
        } else {
            const text = await res.text();
            console.error('saveSede error HTTP ' + res.status, text);
            let msg = 'Error ' + res.status;
            try {
                const err = JSON.parse(text);
                msg = err.errors ? Object.values(err.errors).flat().join('\n') : (err.message || msg);
            } catch (_) { msg += ': ' + text.substring(0, 200); }
            showSedeError(msg);
        }
    } catch(e) { console.error('saveSede excepción:', e); showSedeError('Error de conexión: ' + e.message); }
}

function showSedeError(msg) {
    let el = document.getElementById('sedeFormError');
    if (!el) {
        el = document.createElement('div');
        el.id = 'sedeFormError';
        el.className = 'alert alert-danger py-2 mt-2 mb-0';
        document.getElementById('sedeForm').appendChild(el);
    }
    el.textContent = msg;
    el.style.display = 'block';
}

function clearSedeError() {
    const el = document.getElementById('sedeFormError');
    if (el) el.style.display = 'none';
}

async function deleteSede(id) {
    if (!confirm('¿Eliminar esta sede?')) return;
    try {
        await fetch(`/api/sedes/${id}`, { method: 'DELETE', headers: buildHeaders() });
        loadSedes();
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadEmpresas();
    if (!isAdminTenant) loadSedes();
});

let qrInterval = null;
let qrSedeId   = null;

async function showQR(sedeId, sedeName) {
    qrSedeId = sedeId;
    document.getElementById('qrSedeName').textContent = sedeName;
    document.getElementById('qrCanvas').innerHTML = '';

    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();

    await refreshQR();

    clearInterval(qrInterval);
    qrInterval = setInterval(async () => {
        const remaining = 30 - (Math.floor(Date.now() / 1000) % 30);
        document.getElementById('qrCountdown').textContent = remaining + 's';
        if (remaining === 30) await refreshQR();
    }, 1000);

    document.getElementById('qrModal').addEventListener('hidden.bs.modal', () => {
        clearInterval(qrInterval);
    }, { once: true });
}

async function refreshQR() {
    try {
        const res  = await fetch(`/api/sedes/${qrSedeId}/qr`, { headers: buildHeaders() });
        const data = await res.json();

        document.getElementById('qrCanvas').innerHTML = '';
        new QRCode(document.getElementById('qrCanvas'), {
            text:   data.qr_value,
            width:  220,
            height: 220,
        });

        const remaining = data.expires_in_seconds;
        document.getElementById('qrCountdown').textContent = remaining + 's';
    } catch(e) { console.error('Error generando QR', e); }
}

// ── QR Estático ───────────────────────────────────────────────────────────────
let staticQrSedeId = null;

async function showStaticQR(sedeId, sedeName, isEnabled) {
    staticQrSedeId = sedeId;
    document.getElementById('staticQrSedeName').textContent = sedeName;
    document.getElementById('staticQrCanvas').innerHTML = '';

    const modal = new bootstrap.Modal(document.getElementById('staticQrModal'));
    modal.show();

    if (!isEnabled) {
        document.getElementById('staticQrNotEnabled').classList.remove('d-none');
        document.getElementById('staticQrContent').classList.add('d-none');
        return;
    }

    await loadStaticQR();
}

async function loadStaticQR() {
    try {
        const res  = await fetch(`/api/sedes/${staticQrSedeId}/qr-static`, { headers: buildHeaders() });
        if (!res.ok) { console.error('Error cargando QR estático'); return; }
        const data = await res.json();

        document.getElementById('staticQrNotEnabled').classList.add('d-none');
        document.getElementById('staticQrContent').classList.remove('d-none');
        document.getElementById('staticQrCanvas').innerHTML = '';

        new QRCode(document.getElementById('staticQrCanvas'), {
            text:   data.qr_value,
            width:  280,
            height: 280,
        });
    } catch(e) { console.error(e); }
}

async function enableStaticQR() {
    try {
        await fetch(`/api/sedes/${staticQrSedeId}/qr-static/enable`,
            { method: 'POST', headers: buildHeaders() });
        loadSedes();
        await loadStaticQR();
    } catch(e) { console.error(e); }
}

async function regenerateStaticQR() {
    if (!confirm('¿Regenerar el QR estático? Los QR impresos anteriores quedarán inválidos inmediatamente.')) return;
    try {
        await fetch(`/api/sedes/${staticQrSedeId}/qr-static/regenerar`,
            { method: 'POST', headers: buildHeaders() });
        loadSedes();
        await loadStaticQR();
    } catch(e) { console.error(e); }
}

function printStaticQR() {
    const canvas = document.querySelector('#staticQrCanvas canvas');
    if (!canvas) return;
    const img  = canvas.toDataURL('image/png');
    const name = document.getElementById('staticQrSedeName').textContent;
    const win  = window.open('', '_blank');
    win.document.write(`
        <html><head><title>QR Asistencia - ${name}</title>
        <style>
            body { margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; font-family: sans-serif; }
            img  { width: 300px; height: 300px; }
            h2   { margin-bottom: 12px; }
            p    { font-size: 14px; color: #555; margin: 4px 0; }
            button { margin-top: 20px; padding: 8px 24px; font-size: 16px; cursor: pointer; }
            @media print { button { display: none; } }
        </style></head>
        <body>
            <h2>Marcar Asistencia</h2>
            <img src="${img}"/>
            <p style="font-size:18px; font-weight:bold; margin-top:12px;">${name}</p>
            <p>Escanea con la app BiometricIP</p>
            <button onclick="window.print()">Imprimir</button>
        </body></html>
    `);
    win.document.close();
}

// ── QR Web v3 ─────────────────────────────────────────────────────────────────
let webQrSedeId = null;

async function showWebQR(sedeId, sedeName, isEnabled) {
    webQrSedeId = sedeId;
    document.getElementById('webQrSedeName').textContent = sedeName;
    document.getElementById('webQrCanvas').innerHTML = '';

    const modal = new bootstrap.Modal(document.getElementById('webQrModal'));
    modal.show();

    if (!isEnabled) {
        document.getElementById('webQrNotEnabled').classList.remove('d-none');
        document.getElementById('webQrContent').classList.add('d-none');
        return;
    }

    await loadWebQR();
}

async function loadWebQR() {
    try {
        const res  = await fetch(`/api/sedes/${webQrSedeId}/qr-v3`, { headers: buildHeaders() });
        if (!res.ok) { console.error('Error cargando QR Web'); return; }
        const data = await res.json();

        document.getElementById('webQrNotEnabled').classList.add('d-none');
        document.getElementById('webQrContent').classList.remove('d-none');
        document.getElementById('webQrCanvas').innerHTML = '';

        new QRCode(document.getElementById('webQrCanvas'), {
            text:   data.qr_value,
            width:  280,
            height: 280,
        });
    } catch(e) { console.error(e); }
}

async function enableWebQR() {
    try {
        await fetch(`/api/sedes/${webQrSedeId}/qr-v3/enable`,
            { method: 'POST', headers: buildHeaders() });
        loadSedes();
        await loadWebQR();
    } catch(e) { console.error(e); }
}

async function regenerateWebQR() {
    if (!confirm('¿Regenerar el QR Web? Los QR impresos anteriores quedarán inválidos inmediatamente.')) return;
    try {
        await fetch(`/api/sedes/${webQrSedeId}/qr-v3/regenerar`,
            { method: 'POST', headers: buildHeaders() });
        loadSedes();
        await loadWebQR();
    } catch(e) { console.error(e); }
}

function printWebQR() {
    const canvas = document.querySelector('#webQrCanvas canvas');
    if (!canvas) return;
    const img  = canvas.toDataURL('image/png');
    const name = document.getElementById('webQrSedeName').textContent;
    const win  = window.open('', '_blank');
    win.document.write(`
        <html><head><title>QR Asistencia Web - ${name}</title>
        <style>
            body { margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; font-family: sans-serif; }
            img  { width: 300px; height: 300px; }
            h2   { margin-bottom: 12px; }
            p    { font-size: 14px; color: #555; margin: 4px 0; }
            button { margin-top: 20px; padding: 8px 24px; font-size: 16px; cursor: pointer; }
            @media print { button { display: none; } }
        </style></head>
        <body>
            <h2>Registrar Asistencia</h2>
            <img src="${img}"/>
            <p style="font-size:18px; font-weight:bold; margin-top:12px;">${name}</p>
            <p>Escanea con tu cámara — no necesitas la app</p>
            <button onclick="window.print()">Imprimir</button>
        </body></html>
    `);
    win.document.close();
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&loading=async&callback=googleMapsReady"></script>
@endpush
