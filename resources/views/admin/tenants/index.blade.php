@extends('layouts.admin')
@section('title', 'Tenants')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-database me-2 text-primary"></i>Tenants</h4>
                    <p class="text-muted mb-0">Gestión de empresas y bases de datos multi-tenant</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.tenants.tablas') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-table me-1"></i> Config. Tablas
                    </a>
                    <a href="{{ route('admin.tenants.descargar-sql') }}" class="btn btn-outline-success">
                        <i class="ti ti-download me-1"></i> Descargar SQL
                    </a>
                    <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Nuevo Tenant
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas resumen --}}
    <div class="row mb-4" id="statsRow">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-primary" id="statTotal">—</div>
                    <div class="text-muted small">Total Tenants</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-success" id="statActivos">—</div>
                    <div class="text-muted small">Activos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-warning" id="statInactivos">—</div>
                    <div class="text-muted small">Inactivos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-info" id="statUsuarios">—</div>
                    <div class="text-muted small">Total Usuarios</div>
                </div>
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
                                    <th>ID</th>
                                    <th>Empresa</th>
                                    <th>RUC / NIT</th>
                                    <th>Email</th>
                                    <th>Plan</th>
                                    <th>Usuarios</th>
                                    <th>Base de Datos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tenantsTbody">
                                <tr><td colspan="9" class="text-center text-muted py-3">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Editar Tenant --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-edit me-2"></i>Editar Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">Nombre de la Empresa <span class="text-danger">*</span></label>
                            <input type="text" id="editNombre" class="form-control" placeholder="Empresa S.A.S." required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">RUC / NIT</label>
                            <input type="text" id="editRuc" class="form-control" placeholder="900123456-7">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" id="editEmail" class="form-control" placeholder="admin@empresa.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" id="editTelefono" class="form-control" placeholder="+57 300 000 0000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Plan</label>
                            <select id="editPlan" class="form-select">
                                <option value="bronce">Bronce</option>
                                <option value="plata">Plata</option>
                                <option value="oro">Oro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Máx. Usuarios</label>
                            <input type="number" id="editMaxUsuarios" class="form-control" min="1" value="50">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch ms-2">
                                <input class="form-check-input" type="checkbox" id="editActivo">
                                <label class="form-check-label fw-semibold">Activo</label>
                            </div>
                        </div>
                    </div>
                    <div id="editError" class="alert alert-danger py-2 d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">
                    <i class="ti ti-device-floppy me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Token Agente --}}
<div class="modal fade" id="tokenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-key me-2"></i>Token de Agente Local</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tokenEmpresaId">
                <p class="text-muted small">El token permite al agente local sincronizar marcaciones desde la red interna.</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Vigencia (días)</label>
                    <input type="number" id="tokenDias" class="form-control" value="365" min="1" max="3650">
                </div>
                <div id="tokenResult" class="d-none">
                    <div class="alert alert-success py-2 mb-2">
                        <strong>Token generado:</strong>
                    </div>
                    <div class="input-group mb-2">
                        <input type="text" id="tokenValue" class="form-control font-monospace" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyToken()" title="Copiar">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                    <small id="tokenVigencia" class="text-muted"></small>
                </div>
                <div id="tokenError" class="alert alert-danger py-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" onclick="revocarToken()">
                    <i class="ti ti-trash me-1"></i> Revocar Token
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="generarToken()">
                    <i class="ti ti-refresh me-1"></i> Generar Token
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
    oro:    '<span class="badge bg-warning text-dark">Oro</span>',
    plata:  '<span class="badge bg-secondary">Plata</span>',
    bronce: '<span class="badge" style="background:#cd7f32;color:#fff">Bronce</span>',
};

async function loadTenants() {
    try {
        const res  = await fetch('/api/empresas', { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();
        if (!res.ok) {
            document.getElementById('tenantsTbody').innerHTML =
                `<tr><td colspan="9" class="text-center text-danger py-3">${data.message ?? 'Error al cargar tenants'}</td></tr>`;
            return;
        }

        const empresas = data.data ?? [];
        let total = empresas.length, activos = 0, inactivos = 0, usuarios = 0;

        if (empresas.length === 0) {
            document.getElementById('tenantsTbody').innerHTML =
                '<tr><td colspan="9" class="text-center text-muted py-3">Sin tenants registrados</td></tr>';
        } else {
            document.getElementById('tenantsTbody').innerHTML = empresas.map(e => {
                if (e.is_active) activos++; else inactivos++;
                usuarios += (e.users_count ?? 0);
                return `
                <tr>
                    <td><span class="badge bg-light text-dark border">#${e.id}</span></td>
                    <td><strong>${e.nombre}</strong></td>
                    <td>${e.ruc ?? '—'}</td>
                    <td>${e.email ?? '—'}</td>
                    <td>${planBadge[e.plan] ?? `<span class="badge bg-light text-dark border">${e.plan ?? '—'}</span>`}</td>
                    <td><span class="badge bg-info text-dark">${e.users_count ?? 0} / ${e.max_usuarios ?? '∞'}</span></td>
                    <td><code class="small">${e.tenant?.db_name ?? '—'}</code></td>
                    <td><span class="badge ${e.is_active ? 'bg-success' : 'bg-danger'}">${e.is_active ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick='openEdit(${JSON.stringify(e).replace(/'/g, "&#39;")})' title="Editar">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="openToken(${e.id})" title="Token Agente">
                            <i class="ti ti-key"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');
        }

        document.getElementById('statTotal').textContent     = total;
        document.getElementById('statActivos').textContent   = activos;
        document.getElementById('statInactivos').textContent = inactivos;
        document.getElementById('statUsuarios').textContent  = usuarios;

    } catch(e) {
        console.error(e);
        document.getElementById('tenantsTbody').innerHTML =
            '<tr><td colspan="9" class="text-center text-danger py-3">Error de conexión</td></tr>';
    }
}

function openEdit(e) {
    document.getElementById('editId').value          = e.id;
    document.getElementById('editNombre').value      = e.nombre ?? '';
    document.getElementById('editRuc').value         = e.ruc ?? '';
    document.getElementById('editEmail').value       = e.email ?? '';
    document.getElementById('editTelefono').value    = e.telefono ?? '';
    document.getElementById('editPlan').value        = e.plan ?? 'bronce';
    document.getElementById('editMaxUsuarios').value = e.max_usuarios ?? 50;
    document.getElementById('editActivo').checked    = !!e.is_active;
    document.getElementById('editError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

async function saveEdit() {
    const id = document.getElementById('editId').value;
    const payload = {
        nombre:       document.getElementById('editNombre').value.trim(),
        ruc:          document.getElementById('editRuc').value.trim() || null,
        email:        document.getElementById('editEmail').value.trim() || null,
        telefono:     document.getElementById('editTelefono').value.trim() || null,
        plan:         document.getElementById('editPlan').value,
        max_usuarios: parseInt(document.getElementById('editMaxUsuarios').value) || 50,
        is_active:    document.getElementById('editActivo').checked,
    };

    if (!payload.nombre) {
        showEditError('El nombre de la empresa es obligatorio.');
        return;
    }

    try {
        const res = await fetch(`/api/empresas/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload),
        });

        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            await loadTenants();
        } else {
            const err = await res.json();
            showEditError(err.errors ? Object.values(err.errors).flat().join('\n') : (err.message ?? 'Error al guardar'));
        }
    } catch(e) {
        showEditError('Error de conexión: ' + e.message);
    }
}

function showEditError(msg) {
    const el = document.getElementById('editError');
    el.textContent = msg;
    el.classList.remove('d-none');
}

// ── Token de Agente ───────────────────────────────────────────────────────────
function openToken(empresaId) {
    document.getElementById('tokenEmpresaId').value = empresaId;
    document.getElementById('tokenDias').value      = 365;
    document.getElementById('tokenResult').classList.add('d-none');
    document.getElementById('tokenError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('tokenModal')).show();
}

async function generarToken() {
    const id   = document.getElementById('tokenEmpresaId').value;
    const dias = parseInt(document.getElementById('tokenDias').value) || 365;
    document.getElementById('tokenError').classList.add('d-none');

    try {
        const res  = await fetch(`/api/empresas/${id}/agent-token`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({ vigencia_dias: dias }),
        });
        const data = await res.json();

        if (res.ok) {
            document.getElementById('tokenValue').value            = data.token;
            document.getElementById('tokenVigencia').textContent   = `Vigente hasta: ${data.vigencia}`;
            document.getElementById('tokenResult').classList.remove('d-none');
        } else {
            const el = document.getElementById('tokenError');
            el.textContent = data.message ?? 'Error al generar token';
            el.classList.remove('d-none');
        }
    } catch(e) {
        const el = document.getElementById('tokenError');
        el.textContent = 'Error de conexión: ' + e.message;
        el.classList.remove('d-none');
    }
}

async function revocarToken() {
    if (!confirm('¿Revocar el token de agente? El agente local quedará sin acceso.')) return;
    const id = document.getElementById('tokenEmpresaId').value;

    try {
        const res = await fetch(`/api/empresas/${id}/agent-token`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}` },
        });
        if (res.ok) {
            document.getElementById('tokenResult').classList.add('d-none');
            Swal.fire({ icon: 'success', title: 'Token revocado', timer: 1500, showConfirmButton: false });
        }
    } catch(e) { console.error(e); }
}

function copyToken() {
    const input = document.getElementById('tokenValue');
    input.select();
    document.execCommand('copy');
    Swal.fire({ icon: 'success', title: 'Copiado', timer: 1000, showConfirmButton: false });
}

document.addEventListener('DOMContentLoaded', loadTenants);
</script>
@endpush
