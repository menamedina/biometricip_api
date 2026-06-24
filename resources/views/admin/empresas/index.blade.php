@extends('layouts.admin')
@section('title', 'Empresas')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fa-solid fa-building me-2 text-primary"></i>Empresas</h4>
                    <p class="text-muted mb-0">Gestión multi-tenant de empresas</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empresaModal" onclick="resetForm()">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Empresa
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>RUC / NIT</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Plan</th>
                                <th>Usuarios</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="empresasTbody">
                            <tr><td colspan="9" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <small class="text-muted" id="empresasInfo"></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar -->
<div class="modal fade" id="empresaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="empresaModalTitle">Nueva Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="empresaForm">
                    <input type="hidden" id="empresaId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre de la empresa</label>
                            <input type="text" id="frmNombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">RUC / NIT</label>
                            <input type="text" id="frmRuc" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" id="frmEmail" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" id="frmTelefono" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Plan</label>
                            <select id="frmPlan" class="form-select" onchange="updateMaxUsuarios()">
                                <option value="bronce">Bronce (hasta 50)</option>
                                <option value="plata">Plata (hasta 200)</option>
                                <option value="oro">Oro (más de 200)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Máx. usuarios</label>
                            <input type="number" id="frmMaxUsuarios" class="form-control" min="1" value="50">
                        </div>
                    </div>

                    <!-- Campos solo para nueva empresa -->
                    <div id="adminFields">
                        <hr>
                        <h6 class="text-muted mb-3"><i class="fa-solid fa-user-shield me-1"></i> Administrador de la empresa</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombre admin</label>
                                <input type="text" id="frmAdminName" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email admin</label>
                                <input type="email" id="frmAdminEmail" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contraseña admin</label>
                                <input type="password" id="frmAdminPassword" class="form-control" minlength="6">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveEmpresa()">Guardar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Token Agente -->
<div class="modal fade" id="tokenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-key me-2"></i>Token Agente — <span id="tokenEmpresaNombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tokenEmpresaId">

                <div id="tokenActual" class="mb-3" style="display:none">
                    <label class="form-label text-muted">Token actual</label>
                    <div class="input-group">
                        <input type="text" id="tokenMasked" class="form-control font-monospace" readonly>
                        <button class="btn btn-outline-danger" onclick="revokeToken()" title="Revocar token">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                    <small class="text-muted" id="tokenVigencia"></small>
                </div>

                <div id="tokenSinToken" class="alert alert-warning mb-3" style="display:none">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> No hay token generado para esta empresa.
                </div>

                <div id="tokenNuevo" class="mb-3" style="display:none">
                    <label class="form-label text-success fw-bold">Token generado — cópialo ahora, no se volverá a mostrar completo</label>
                    <div class="input-group">
                        <input type="text" id="tokenNuevoValor" class="form-control font-monospace" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyToken()"><i class="fa-solid fa-copy"></i></button>
                    </div>
                </div>

                <hr>
                <label class="form-label">Vigencia (días)</label>
                <input type="number" id="tokenDias" class="form-control" value="365" min="1" max="3650">
                <small class="text-muted">Por defecto 365 días.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="generateToken()">
                    <i class="fa-solid fa-rotate me-1"></i> Generar nuevo token
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');

const planBadge = {
    bronce: '<span class="badge bg-warning text-dark">Bronce</span>',
    plata:  '<span class="badge bg-secondary text-white">Plata</span>',
    oro:    '<span class="badge bg-warning" style="background:linear-gradient(135deg,#f59e0b,#d97706)!important">Oro</span>',
};

function updateMaxUsuarios() {
    const plan = document.getElementById('frmPlan').value;
    const maxInput = document.getElementById('frmMaxUsuarios');
    const defaults = { bronce: 50, plata: 200, oro: 500 };
    maxInput.value = defaults[plan] || 50;
}

function resetForm() {
    document.getElementById('empresaForm').reset();
    document.getElementById('empresaId').value = '';
    document.getElementById('adminFields').style.display = '';
    document.getElementById('frmAdminEmail').required = true;
    document.getElementById('frmAdminPassword').required = true;
    document.getElementById('empresaModalTitle').textContent = 'Nueva Empresa';
}

async function loadEmpresas() {
    try {
        const res = await fetch('/api/empresas', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        console.log('Empresas response:', res.status, data);
        if (!res.ok) {
            document.getElementById('empresasTbody').innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">${data.message || 'Error al cargar'}</td></tr>`;
            return;
        }
        const tbody = document.getElementById('empresasTbody');
        const empresas = data.data || [];

        if (empresas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">Sin empresas</td></tr>';
        } else {
            tbody.innerHTML = empresas.map(e => `
                <tr>
                    <td>${e.id}</td>
                    <td><strong>${e.nombre}</strong></td>
                    <td>${e.ruc || '—'}</td>
                    <td>${e.email || '—'}</td>
                    <td>${e.telefono || '—'}</td>
                    <td>${planBadge[e.plan] || '<span class="badge bg-light text-dark">—</span>'}</td>
                    <td><span class="badge ${(e.users_count || 0) >= (e.max_usuarios || 50) ? 'bg-danger' : 'bg-info'}">${e.users_count || 0} / ${e.max_usuarios || 50}</span></td>
                    <td><span class="badge ${e.is_active ? 'bg-success' : 'bg-danger'}">${e.is_active ? 'Activa' : 'Inactiva'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editEmpresa(${e.id})"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="openTokenModal(${e.id}, '${e.nombre}', ${JSON.stringify(e.agent_token ? '****' + e.agent_token.slice(-6) : null)}, ${JSON.stringify(e.agent_token_vigencia || null)})" title="Token agente"><i class="fa-solid fa-key"></i></button>
                        ${e.is_active
                            ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteEmpresa(${e.id})"><i class="fa-solid fa-ban"></i></button>`
                            : `<button class="btn btn-sm btn-outline-success" onclick="activarEmpresa(${e.id})"><i class="fa-solid fa-check"></i></button>`
                        }
                    </td>
                </tr>
            `).join('');
        }
        document.getElementById('empresasInfo').textContent = `${empresas.length} empresa${empresas.length !== 1 ? 's' : ''}`;
    } catch(e) { console.error(e); }
}

async function editEmpresa(id) {
    try {
        const res = await fetch(`/api/empresas/${id}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        const e = data.data;

        document.getElementById('empresaId').value    = e.id;
        document.getElementById('frmNombre').value    = e.nombre || '';
        document.getElementById('frmRuc').value        = e.ruc || '';
        document.getElementById('frmEmail').value      = e.email || '';
        document.getElementById('frmTelefono').value   = e.telefono || '';
        document.getElementById('frmPlan').value       = e.plan || 'bronce';
        document.getElementById('frmMaxUsuarios').value = e.max_usuarios || 50;

        // Ocultar campos de admin al editar
        document.getElementById('adminFields').style.display = 'none';
        document.getElementById('frmAdminEmail').required = false;
        document.getElementById('frmAdminPassword').required = false;

        document.getElementById('empresaModalTitle').textContent = 'Editar Empresa';
        new bootstrap.Modal(document.getElementById('empresaModal')).show();
    } catch(e) { console.error(e); }
}

async function saveEmpresa() {
    const id = document.getElementById('empresaId').value;
    const payload = {
        nombre:   document.getElementById('frmNombre').value,
        ruc:      document.getElementById('frmRuc').value || null,
        email:    document.getElementById('frmEmail').value || null,
        telefono: document.getElementById('frmTelefono').value || null,
        plan:          document.getElementById('frmPlan').value,
        max_usuarios:  parseInt(document.getElementById('frmMaxUsuarios').value) || 50,
    };

    if (!id) {
        payload.admin_name     = document.getElementById('frmAdminName').value || null;
        payload.admin_email    = document.getElementById('frmAdminEmail').value;
        payload.admin_password = document.getElementById('frmAdminPassword').value;
    }

    const url    = id ? `/api/empresas/${id}` : '/api/empresas';
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('empresaModal')).hide();
            loadEmpresas();
        } else {
            const err = await res.json();
            alert(Object.values(err.errors || {}).flat().join('\n') || err.message || 'Error');
        }
    } catch(e) { console.error(e); }
}

async function deleteEmpresa(id) {
    if (!confirm('¿Desactivar esta empresa?')) return;
    try {
        await fetch(`/api/empresas/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}` }
        });
        loadEmpresas();
    } catch(e) { console.error(e); }
}

async function activarEmpresa(id) {
    try {
        await fetch(`/api/empresas/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({ is_active: true })
        });
        loadEmpresas();
    } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', loadEmpresas);

// --- Token Agente ---

function openTokenModal(id, nombre, tokenMasked, vigencia) {
    document.getElementById('tokenEmpresaId').value        = id;
    document.getElementById('tokenEmpresaNombre').textContent = nombre;
    document.getElementById('tokenNuevo').style.display    = 'none';
    document.getElementById('tokenNuevoValor').value       = '';

    if (tokenMasked) {
        document.getElementById('tokenActual').style.display    = '';
        document.getElementById('tokenSinToken').style.display  = 'none';
        document.getElementById('tokenMasked').value            = tokenMasked;
        document.getElementById('tokenVigencia').textContent    = vigencia ? `Vigente hasta: ${vigencia}` : '';
    } else {
        document.getElementById('tokenActual').style.display    = 'none';
        document.getElementById('tokenSinToken').style.display  = '';
    }

    new bootstrap.Modal(document.getElementById('tokenModal')).show();
}

async function generateToken() {
    const id   = document.getElementById('tokenEmpresaId').value;
    const dias = parseInt(document.getElementById('tokenDias').value) || 365;

    try {
        const res = await fetch(`/api/empresas/${id}/agent-token`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({ vigencia_dias: dias }),
        });
        const data = await res.json();

        if (res.ok) {
            document.getElementById('tokenNuevoValor').value    = data.token;
            document.getElementById('tokenNuevo').style.display = '';
            document.getElementById('tokenActual').style.display   = '';
            document.getElementById('tokenSinToken').style.display = 'none';
            document.getElementById('tokenMasked').value           = '****' + data.token.slice(-6);
            document.getElementById('tokenVigencia').textContent   = `Vigente hasta: ${data.vigencia}`;
            loadEmpresas();
        } else {
            alert(data.message || 'Error generando token');
        }
    } catch(e) { console.error(e); }
}

async function revokeToken() {
    if (!confirm('¿Revocar el token? El agente dejará de funcionar hasta generar uno nuevo.')) return;
    const id = document.getElementById('tokenEmpresaId').value;

    try {
        const res = await fetch(`/api/empresas/${id}/agent-token`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}` },
        });
        if (res.ok) {
            document.getElementById('tokenActual').style.display   = 'none';
            document.getElementById('tokenSinToken').style.display = '';
            document.getElementById('tokenNuevo').style.display    = 'none';
            loadEmpresas();
        }
    } catch(e) { console.error(e); }
}

function copyToken() {
    const val = document.getElementById('tokenNuevoValor').value;
    navigator.clipboard.writeText(val).then(() => alert('Token copiado al portapapeles'));
}
</script>
@endpush
