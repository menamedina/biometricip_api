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
                @if(auth()->user()->admin_tenant)
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empresaModal" onclick="resetForm()">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Empresa
                </button>
                @else
                <button class="btn btn-primary" disabled title="Solo administradores multi-empresa pueden crear empresas">
                    <i class="fa-solid fa-plus me-1"></i> Nueva Empresa
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 w-100" id="empresasTable">
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
                    @if(!auth()->user()->admin_tenant)
                    <tr><td colspan="9" class="text-center text-danger py-3">
                        <i class="fa-solid fa-lock me-2"></i>No tienes permiso para ver esta información.
                    </td></tr>
                    @else
                    <tr id="trLoadingEmp">
                        <td colspan="9" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;"></div>
                            <p class="text-muted mt-2 mb-0 small">Cargando empresas...</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
div.dataTables_wrapper div.dataTables_length,
div.dataTables_wrapper div.dataTables_filter {
    padding: 12px 16px 0;
}
div.dataTables_wrapper div.dataTables_info,
div.dataTables_wrapper div.dataTables_paginate {
    padding: 10px 16px 12px;
    border-top: 1px solid #e9ecef;
}
div.dataTables_wrapper div.dataTables_length label,
div.dataTables_wrapper div.dataTables_filter label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 8px;
}
div.dataTables_wrapper div.dataTables_info {
    font-size: 13px;
    color: #6c757d;
}
#empresasTable th, #empresasTable td {
    font-size: 13px;
    vertical-align: middle;
    white-space: nowrap;
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
var tablaEmpresas = null;

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
        const res = await fetch('/admin/empresas/list');
        const data = await res.json();
        if (!res.ok) {
            var trLoading = document.getElementById('trLoadingEmp');
            if (trLoading) trLoading.remove();
            document.getElementById('empresasTbody').innerHTML =
                '<tr><td colspan="9" class="text-center text-danger py-3">' + (data.message || 'Error al cargar') + '</td></tr>';
            return;
        }
        const items = data.data || [];

        var trLoading = document.getElementById('trLoadingEmp');
        if (trLoading) trLoading.remove();

        if ($.fn.DataTable.isDataTable('#empresasTable')) {
            tablaEmpresas.clear().rows.add(items).draw();
        } else {
            tablaEmpresas = $('#empresasTable').DataTable({
                data: items,
                processing: true,
                order: [[0, 'asc']],
                scrollX: true,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    lengthMenu: 'Mostrar _MENU_ registros',
                    zeroRecords: 'Sin empresas',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros)',
                    search: 'Buscar:',
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
                    processing: 'Procesando...',
                },
                initComplete: function() {
                    $('#empresasTable_length select').addClass('form-select form-select-sm d-inline-block w-auto');
                    $('#empresasTable_filter input').addClass('form-control form-control-sm d-inline-block w-auto');
                    $('#empresasTable_filter').prepend(
                        '<button class="btn btn-sm btn-outline-secondary me-2" onclick="loadEmpresas()" title="Recargar tabla"><i class="ti ti-refresh"></i></button>'
                    );
                },
                columns: [
                    { title: 'ID',        data: 'id',       render: function(d) { return d; } },
                    { title: 'Nombre',    data: 'nombre',   render: function(d) { return '<strong>' + (d || '') + '</strong>'; } },
                    { title: 'RUC / NIT', data: 'ruc',      render: function(d) { return d || '—'; } },
                    { title: 'Email',     data: 'email',    render: function(d) { return d || '—'; } },
                    { title: 'Teléfono', data: 'telefono', render: function(d) { return d || '—'; } },
                    {
                        title: 'Plan', data: 'plan',
                        render: function(d, type) {
                            if (type !== 'display') return d || '';
                            return planBadge[d] || '<span class="badge bg-light text-dark">—</span>';
                        }
                    },
                    {
                        title: 'Usuarios', data: 'users_count',
                        render: function(d, type, row) {
                            var count = d || 0;
                            var max   = row.max_usuarios || 50;
                            if (type !== 'display') return count;
                            return '<span class="badge ' + (count >= max ? 'bg-danger' : 'bg-info') + '">' + count + ' / ' + max + '</span>';
                        }
                    },
                    {
                        title: 'Estado', data: 'is_active',
                        render: function(d, type) {
                            if (type !== 'display') return d ? 1 : 0;
                            return '<span class="badge ' + (d ? 'bg-success' : 'bg-danger') + '">' + (d ? 'Activa' : 'Inactiva') + '</span>';
                        }
                    },
                    {
                        title: 'Acciones', data: null, orderable: false,
                        render: function(d, type, row) {
                            var tokenMasked = row.agent_token ? '****' + row.agent_token.slice(-6) : null;
                            var vigencia    = row.agent_token_vigencia || null;
                            var btnEdit  = '<button class="btn btn-sm btn-outline-primary me-1" onclick="editEmpresa(' + row.id + ')"><i class="fa-solid fa-pen"></i></button>';
                            var btnToken = '<button class="btn btn-sm btn-outline-secondary me-1" onclick="openTokenModal(' + row.id + ',\'' + (row.nombre || '').replace(/'/g,'') + '\',' + JSON.stringify(tokenMasked) + ',' + JSON.stringify(vigencia) + ')" title="Token agente"><i class="fa-solid fa-key"></i></button>';
                            var btnToggle = row.is_active
                                ? '<button class="btn btn-sm btn-outline-danger" onclick="deleteEmpresa(' + row.id + ')"><i class="fa-solid fa-ban"></i></button>'
                                : '<button class="btn btn-sm btn-outline-success" onclick="activarEmpresa(' + row.id + ')"><i class="fa-solid fa-check"></i></button>';
                            return '<div class="d-flex flex-nowrap gap-1">' + btnEdit + btnToken + btnToggle + '</div>';
                        }
                    }
                ]
            });
        }
    } catch(e) {
        console.error(e);
    }
}

async function editEmpresa(id) {
    try {
        const res = await fetch(`/admin/empresas/${id}`);
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

    const url    = id ? `/admin/empresas/${id}` : '/admin/empresas';
    const method = id ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
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
        await fetch(`/admin/empresas/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        loadEmpresas();
    } catch(e) { console.error(e); }
}

async function activarEmpresa(id) {
    try {
        await fetch(`/admin/empresas/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ is_active: true })
        });
        loadEmpresas();
    } catch(e) { console.error(e); }
}

@if(auth()->user()->admin_tenant)
document.addEventListener('DOMContentLoaded', () => loadEmpresas());
@endif

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
        const res = await fetch(`/admin/empresas/${id}/agent-token`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
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
        const res = await fetch(`/admin/empresas/${id}/agent-token`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
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
