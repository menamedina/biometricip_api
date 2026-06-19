@extends('layouts.admin')
@section('title', 'Dispositivos Biométricos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-fingerprint me-2 text-primary"></i>Dispositivos Biométricos</h4>
                    <p class="text-muted mb-0">Gestión de dispositivos ZKTeco y sincronización de asistencias</p>
                </div>
                <div>
                    <button class="btn btn-outline-info me-2" onclick="openPingModal()">
                        <i class="fa-solid fa-wifi me-1"></i> Probar IP
                    </button>
                    <button class="btn btn-primary" onclick="openDeviceModal()">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo Dispositivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cards resumen --}}
    <div class="row g-3 mb-3" id="statsCards">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fa-solid fa-server text-primary fs-5"></i>
                        </div>
                        <div>
                            <h4 class="mb-0" id="statTotal">0</h4>
                            <p class="text-muted mb-0 small">Dispositivos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                        </div>
                        <div>
                            <h4 class="mb-0" id="statActivos">0</h4>
                            <p class="text-muted mb-0 small">Activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fa-solid fa-rotate text-info fs-5"></i>
                        </div>
                        <div>
                            <h4 class="mb-0" id="statLastSync">--</h4>
                            <p class="text-muted mb-0 small">Última Sync</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fa-solid fa-users text-warning fs-5"></i>
                        </div>
                        <div>
                            <h4 class="mb-0" id="statUsuarios">0</h4>
                            <p class="text-muted mb-0 small">Usuarios en dispositivos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de dispositivos --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>IP</th>
                                    <th>Modelo</th>
                                    <th>Serial</th>
                                    <th>Sede</th>
                                    <th>Última Sync</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="devicesTbody">
                                <tr><td colspan="8" class="text-center text-muted py-3">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Nuevo/Editar dispositivo --}}
<div class="modal fade" id="deviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deviceModalTitle">Nuevo Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deviceForm">
                    <input type="hidden" id="deviceId">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="deviceNombre" class="form-control" placeholder="Ej: MB160 Recepción" required>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Dirección IP</label>
                            <input type="text" id="deviceIp" class="form-control" placeholder="10.1.40.23" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Puerto</label>
                            <input type="number" id="devicePuerto" class="form-control" value="4370">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sede</label>
                        <select id="deviceSede" class="form-select" required>
                            <option value="">Seleccionar sede...</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3" id="deviceActivoGroup" style="display:none;">
                        <input class="form-check-input" type="checkbox" id="deviceActivo" checked>
                        <label class="form-check-label">Activo</label>
                    </div>
                </form>
                <div id="deviceFormError" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveDevice()">
                    <i class="fa-solid fa-save me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Probar IP (ping) --}}
<div class="modal fade" id="pingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-wifi me-2"></i>Probar Conexión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-8 mb-3">
                        <label class="form-label">Dirección IP</label>
                        <input type="text" id="pingIp" class="form-control" placeholder="10.1.40.23">
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">Puerto</label>
                        <input type="number" id="pingPuerto" class="form-control" value="4370">
                    </div>
                </div>
                <button class="btn btn-info w-100" onclick="doPing()" id="pingBtn">
                    <i class="fa-solid fa-satellite-dish me-1"></i> Probar Conexión
                </button>
                <div id="pingResult" class="mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Usuarios del dispositivo --}}
<div class="modal fade" id="usersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-users me-2"></i>Usuarios en Dispositivo: <span id="usersDeviceName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-3 mb-3">
                    <span class="badge bg-primary fs-6" id="usersTotal">0 total</span>
                    <span class="badge bg-success fs-6" id="usersVinculados">0 vinculados</span>
                    <span class="badge bg-warning text-dark fs-6" id="usersSinVincular">0 sin vincular</span>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>ID Dispositivo</th>
                                <th>Nombre Dispositivo</th>
                                <th>Vinculado</th>
                                <th>Empleado Sistema</th>
                            </tr>
                        </thead>
                        <tbody id="usersTbody">
                            <tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Vincular empleado --}}
<div class="modal fade" id="vincularModal" tabindex="-1" style="z-index:1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-link me-2"></i>Vincular Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="vincularCedula">
                <p class="text-muted small mb-3">
                    Asignando cédula <strong id="vincularCedulaLabel" class="text-primary"></strong>
                    al empleado que selecciones.
                </p>
                <div class="mb-3">
                    <label class="form-label">Buscar empleado</label>
                    <input type="text" id="vincularSearch" class="form-control" placeholder="Nombre o correo..." oninput="searchEmpleados()">
                </div>
                <div id="vincularResults" style="max-height:250px; overflow-y:auto;"></div>
                <div id="vincularError" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Historial de sincronización --}}
<div class="modal fade" id="syncHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de Sync: <span id="syncDeviceName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Nuevos</th>
                                <th>Total</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="syncHistoryTbody">
                            <tr><td colspan="5" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
let sedesCache = [];

// ── Cargar sedes para el select ──────────────────────────────────────────────
async function loadSedes() {
    try {
        const res = await fetch('/api/sedes', { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        sedesCache = data.data || [];
        const select = document.getElementById('deviceSede');
        select.innerHTML = '<option value="">Seleccionar sede...</option>' +
            sedesCache.map(s => `<option value="${s.id}">${s.nombre}</option>`).join('');
    } catch(e) { console.error(e); }
}

// ── Cargar dispositivos ──────────────────────────────────────────────────────
async function loadDevices() {
    try {
        const res = await fetch('/api/devices', { headers: { 'Authorization': `Bearer ${token}` } });
        const devices = await res.json();
        const tbody = document.getElementById('devicesTbody');

        // Stats
        document.getElementById('statTotal').textContent = devices.length;
        document.getElementById('statActivos').textContent = devices.filter(d => d.is_active).length;

        const lastSync = devices
            .filter(d => d.ultima_sync)
            .sort((a, b) => new Date(b.ultima_sync) - new Date(a.ultima_sync))[0];
        document.getElementById('statLastSync').textContent = lastSync
            ? new Date(lastSync.ultima_sync).toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' })
            : '--';

        if (devices.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Sin dispositivos registrados</td></tr>';
            return;
        }

        tbody.innerHTML = devices.map(d => `
            <tr>
                <td><strong>${d.nombre}</strong></td>
                <td><code>${d.ip}:${d.puerto}</code></td>
                <td>${d.modelo || '—'}</td>
                <td><small>${d.numero_serie || '—'}</small></td>
                <td><span class="badge bg-primary">${d.sede?.nombre || '—'}</span></td>
                <td>${d.ultima_sync ? new Date(d.ultima_sync).toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' }) : '<span class="text-muted">Nunca</span>'}</td>
                <td><span class="badge ${d.is_active ? 'bg-success' : 'bg-danger'}">${d.is_active ? 'Activo' : 'Inactivo'}</span></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-success" onclick="syncDevice(${d.id}, '${d.nombre}')" title="Sincronizar asistencias">
                            <i class="fa-solid fa-rotate"></i> Sync
                        </button>
                        <button class="btn btn-outline-danger" onclick="clearDevice(${d.id}, '${d.nombre}')" title="Vaciar registros del biométrico">
                            <i class="fa-solid fa-trash-can"></i> Vaciar
                        </button>
                        <button class="btn btn-outline-info" onclick="testDevice(${d.id})" title="Probar conexión">
                            <i class="fa-solid fa-wifi"></i>
                        </button>
                        <button class="btn btn-outline-primary" onclick="showDeviceUsers(${d.id}, '${d.nombre}')" title="Ver usuarios">
                            <i class="fa-solid fa-users"></i>
                        </button>
                        <button class="btn btn-outline-secondary" onclick="showSyncHistory(${d.id}, '${d.nombre}')" title="Historial sync">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick='editDevice(${JSON.stringify(d).replace(/'/g, "&#39;")})' title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteDevice(${d.id})" title="Eliminar dispositivo">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch(e) { console.error(e); }
}

// ── CRUD Dispositivo ─────────────────────────────────────────────────────────
function openDeviceModal(data = null) {
    document.getElementById('deviceForm').reset();
    document.getElementById('deviceId').value = '';
    document.getElementById('devicePuerto').value = 4370;
    document.getElementById('deviceModalTitle').textContent = 'Nuevo Dispositivo';
    document.getElementById('deviceActivoGroup').style.display = 'none';
    document.getElementById('deviceFormError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('deviceModal')).show();
}

function editDevice(d) {
    document.getElementById('deviceId').value = d.id;
    document.getElementById('deviceNombre').value = d.nombre;
    document.getElementById('deviceIp').value = d.ip;
    document.getElementById('devicePuerto').value = d.puerto;
    document.getElementById('deviceSede').value = d.sede_id;
    document.getElementById('deviceActivo').checked = d.is_active;
    document.getElementById('deviceActivoGroup').style.display = 'block';
    document.getElementById('deviceModalTitle').textContent = 'Editar Dispositivo';
    document.getElementById('deviceFormError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('deviceModal')).show();
}

async function saveDevice() {
    const id = document.getElementById('deviceId').value;
    const payload = {
        nombre: document.getElementById('deviceNombre').value.trim(),
        ip: document.getElementById('deviceIp').value.trim(),
        puerto: parseInt(document.getElementById('devicePuerto').value) || 4370,
        sede_id: parseInt(document.getElementById('deviceSede').value),
    };

    if (id) payload.is_active = document.getElementById('deviceActivo').checked;

    if (!payload.nombre || !payload.ip || !payload.sede_id) {
        showDeviceError('Todos los campos son obligatorios.');
        return;
    }

    const url = id ? `/api/devices/${id}` : '/api/devices';
    const method = id ? 'PUT' : 'POST';

    try {
        const btn = document.querySelector('#deviceModal .btn-primary');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Conectando...';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload),
        });

        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-save me-1"></i> Guardar';

        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('deviceModal')).hide();
            loadDevices();
        } else {
            const err = await res.json();
            showDeviceError(err.message || 'Error al guardar.');
        }
    } catch(e) {
        showDeviceError('Error de conexión: ' + e.message);
    }
}

async function deleteDevice(id) {
    const result = await Swal.fire({
        title: '¿Eliminar dispositivo?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;
    try {
        await fetch(`/api/devices/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
        Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false });
        loadDevices();
    } catch(e) { console.error(e); }
}

function showDeviceError(msg) {
    const el = document.getElementById('deviceFormError');
    el.textContent = msg;
    el.style.display = 'block';
}

// ── Probar conexión (ping) ───────────────────────────────────────────────────
function openPingModal() {
    document.getElementById('pingIp').value = '';
    document.getElementById('pingPuerto').value = 4370;
    document.getElementById('pingResult').style.display = 'none';
    new bootstrap.Modal(document.getElementById('pingModal')).show();
}

async function doPing() {
    const ip = document.getElementById('pingIp').value.trim();
    const puerto = parseInt(document.getElementById('pingPuerto').value) || 4370;
    if (!ip) { alert('Ingresa una IP.'); return; }

    const btn = document.getElementById('pingBtn');
    const result = document.getElementById('pingResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Conectando...';
    result.style.display = 'none';

    try {
        const res = await fetch('/api/devices/ping', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({ ip, puerto }),
        });
        const data = await res.json();

        if (res.ok && data.connected) {
            result.innerHTML = `
                <div class="alert alert-success py-2 mb-0">
                    <i class="fa-solid fa-circle-check me-1"></i> <strong>Conexión exitosa</strong>
                    <hr class="my-2">
                    <div class="row small">
                        <div class="col-6"><strong>Modelo:</strong> ${data.info.nombre}</div>
                        <div class="col-6"><strong>Serial:</strong> ${data.info.serial}</div>
                        <div class="col-6"><strong>Plataforma:</strong> ${data.info.plataforma}</div>
                        <div class="col-6"><strong>Firmware:</strong> ${data.info.firmware}</div>
                        <div class="col-6"><strong>Usuarios:</strong> ${data.usuarios_dispositivo}</div>
                        <div class="col-6"><strong>Registros:</strong> ${data.registros_asistencia}</div>
                    </div>
                </div>`;
        } else {
            result.innerHTML = `<div class="alert alert-danger py-2 mb-0">
                <i class="fa-solid fa-circle-xmark me-1"></i> ${data.message || 'No se pudo conectar.'}
            </div>`;
        }
    } catch(e) {
        result.innerHTML = `<div class="alert alert-danger py-2 mb-0">Error: ${e.message}</div>`;
    }

    result.style.display = 'block';
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-satellite-dish me-1"></i> Probar Conexión';
}

// ── Test conexión dispositivo registrado ─────────────────────────────────────
async function testDevice(id) {
    const row = event.target.closest('tr');
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const res = await fetch(`/api/devices/${id}/test`, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();

        if (res.ok && data.connected) {
            Swal.fire({ icon: 'success', title: 'Conexión exitosa', html: `Usuarios: <b>${data.usuarios_dispositivo}</b><br>Registros: <b>${data.registros_asistencia}</b>` });
        } else {
            Swal.fire({ icon: 'error', title: 'Sin conexión', text: data.message || 'No se pudo conectar.' });
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.message });
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-wifi"></i>';
}

// ── Sincronizar asistencias ──────────────────────────────────────────────────
async function syncDevice(id, nombre) {
    const result = await Swal.fire({
        title: '¿Sincronizar asistencias?',
        text: `Se descargarán todos los registros del dispositivo "${nombre}".`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, sincronizar',
        cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;

    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const res = await fetch(`/api/devices/${id}/sync`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json();

        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Sincronización completada',
                html: `Nuevos registros: <b>${data.registros_nuevos}</b><br>
                       Total procesados: <b>${data.registros_total}</b><br>
                       Omitidos (duplicados): <b>${data.omitidos}</b><br>
                       Sin usuario en sistema: <b>${data.sin_usuario}</b>`,
            });
            loadDevices();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al sincronizar.' });
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.message });
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Sync';
}

// ── Vaciar registros del biométrico ─────────────────────────────────────────
async function clearDevice(id, nombre) {
    const result = await Swal.fire({
        title: '¿Vaciar biométrico?',
        html: `Se eliminarán <b>todos</b> los registros del dispositivo "<b>${nombre}</b>".<br><br>
               <small class="text-muted">Los registros ya sincronizados en BiometricIP <b>NO</b> se eliminan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, vaciar',
        cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;

    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const res = await fetch(`/api/devices/${id}/clear`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}` },
        });
        const data = await res.json();

        if (res.ok) {
            Swal.fire({ icon: 'success', title: 'Biométrico vaciado', text: data.message, timer: 2000, showConfirmButton: false });
            loadDevices();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo vaciar.' });
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: e.message });
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Vaciar';
}

// ── Usuarios del dispositivo ─────────────────────────────────────────────────
let currentDeviceId = null;
let currentDeviceName = null;

async function showDeviceUsers(id, nombre) {
    currentDeviceId = id;
    currentDeviceName = nombre;
    document.getElementById('usersDeviceName').textContent = nombre;
    document.getElementById('usersTbody').innerHTML = '<tr><td colspan="4" class="text-center py-3"><i class="fa-solid fa-spinner fa-spin me-1"></i> Consultando dispositivo...</td></tr>';
    new bootstrap.Modal(document.getElementById('usersModal')).show();

    try {
        const res = await fetch(`/api/devices/${id}/users`, { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();

        document.getElementById('usersTotal').textContent = data.total + ' total';
        document.getElementById('usersVinculados').textContent = data.vinculados + ' vinculados';
        document.getElementById('usersSinVincular').textContent = (data.total - data.vinculados) + ' sin vincular';
        document.getElementById('statUsuarios').textContent = data.total;

        const tbody = document.getElementById('usersTbody');
        if (!data.usuarios?.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin usuarios</td></tr>';
            return;
        }

        tbody.innerHTML = data.usuarios.map(u => `
            <tr>
                <td><code>${u.id_dispositivo || '<span class="text-muted">Sin ID</span>'}</code></td>
                <td>${u.nombre_dispositivo || '—'}</td>
                <td>
                    ${u.vinculado
                        ? '<span class="badge bg-success"><i class="fa-solid fa-link me-1"></i>Vinculado</span>'
                        : `<button class="btn btn-sm btn-outline-warning" onclick="openVincular('${u.id_dispositivo}')">
                               <i class="fa-solid fa-link me-1"></i>Vincular
                           </button>`}
                </td>
                <td>${u.nombre_sistema || '<span class="text-muted">—</span>'}</td>
            </tr>
        `).join('');
    } catch(e) {
        document.getElementById('usersTbody').innerHTML = `<tr><td colspan="4" class="text-center text-danger py-3">Error: ${e.message}</td></tr>`;
    }
}

// ── Historial de sincronización ──────────────────────────────────────────────
async function showSyncHistory(id, nombre) {
    document.getElementById('syncDeviceName').textContent = nombre;
    document.getElementById('syncHistoryTbody').innerHTML = '<tr><td colspan="5" class="text-center py-3"><i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando...</td></tr>';
    new bootstrap.Modal(document.getElementById('syncHistoryModal')).show();

    try {
        const res = await fetch(`/api/devices/${id}/sync-history`, { headers: { 'Authorization': `Bearer ${token}` } });
        const logs = await res.json();
        const tbody = document.getElementById('syncHistoryTbody');

        if (!logs.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin sincronizaciones</td></tr>';
            return;
        }

        tbody.innerHTML = logs.map(l => `
            <tr>
                <td>${new Date(l.created_at).toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' })}</td>
                <td><span class="badge ${l.status === 'ok' ? 'bg-success' : 'bg-danger'}">${l.status}</span></td>
                <td><strong>${l.registros_nuevos}</strong></td>
                <td>${l.registros_total}</td>
                <td><small class="text-muted">${l.mensaje || '—'}</small></td>
            </tr>
        `).join('');
    } catch(e) {
        document.getElementById('syncHistoryTbody').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Error: ${e.message}</td></tr>`;
    }
}

// ── Vincular empleado ────────────────────────────────────────────────────────
let vincularDeviceId = null;

function openVincular(cedula) {
    document.getElementById('vincularCedula').value = cedula;
    document.getElementById('vincularCedulaLabel').textContent = cedula;
    document.getElementById('vincularSearch').value = '';
    document.getElementById('vincularResults').innerHTML = '';
    document.getElementById('vincularError').style.display = 'none';

    // Cerrar modal de usuarios y abrir vincular al terminar
    const usersModal = bootstrap.Modal.getInstance(document.getElementById('usersModal'));
    document.getElementById('usersModal').addEventListener('hidden.bs.modal', () => {
        new bootstrap.Modal(document.getElementById('vincularModal')).show();
    }, { once: true });
    usersModal.hide();
}

let searchTimeout = null;
function searchEmpleados() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        const q = document.getElementById('vincularSearch').value.trim();
        if (q.length < 2) {
            document.getElementById('vincularResults').innerHTML = '';
            return;
        }

        try {
            const res = await fetch(`/api/empleados?search=${encodeURIComponent(q)}&per_page=10`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await res.json();
            const empleados = data.data || [];
            const container = document.getElementById('vincularResults');

            if (!empleados.length) {
                container.innerHTML = '<p class="text-muted small text-center py-2">Sin resultados</p>';
                return;
            }

            container.innerHTML = empleados.map(e => `
                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">
                    <div>
                        <strong>${e.name}</strong>
                        <br><small class="text-muted">${e.email} ${e.cedula ? '· Cédula: ' + e.cedula : '· <span class="text-warning">Sin cédula</span>'}</small>
                    </div>
                    <button class="btn btn-sm btn-success ms-2" onclick="asignarCedula(${e.id}, '${e.name}')">
                        <i class="fa-solid fa-link me-1"></i>Vincular
                    </button>
                </div>
            `).join('');
        } catch(e) { console.error(e); }
    }, 300);
}

async function asignarCedula(empleadoId, nombre) {
    const cedula = document.getElementById('vincularCedula').value;
    const result = await Swal.fire({
        title: '¿Vincular empleado?',
        html: `Se asignará la cédula <b>${cedula}</b> al empleado <b>${nombre}</b>.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, vincular',
        cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/api/empleados/${empleadoId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({ cedula }),
        });

        if (res.ok) {
            const vincularModal = bootstrap.Modal.getInstance(document.getElementById('vincularModal'));
            document.getElementById('vincularModal').addEventListener('hidden.bs.modal', () => {
                showDeviceUsers(currentDeviceId, currentDeviceName);
            }, { once: true });
            vincularModal.hide();
        } else {
            const err = await res.json();
            document.getElementById('vincularError').textContent = err.message || 'Error al asignar.';
            document.getElementById('vincularError').style.display = 'block';
        }
    } catch(e) {
        document.getElementById('vincularError').textContent = 'Error: ' + e.message;
        document.getElementById('vincularError').style.display = 'block';
    }
}

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadSedes();
    loadDevices();
});
</script>
@endpush
