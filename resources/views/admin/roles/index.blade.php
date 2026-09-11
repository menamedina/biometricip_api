@extends('layouts.admin')

@section('title', 'Roles y Permisos')

@push('styles')
<style>
    .role-item {
        cursor: pointer;
        transition: background 0.15s;
        border-left: 3px solid transparent;
    }
    .role-item:hover { background: #f8f9fa; }
    .role-item.active {
        background: #e8f7f4;
        border-left-color: #1ab394;
    }
    .role-item .role-name { font-weight: 600; color: #333; font-size: .9rem; }
    .role-item .role-count { font-size: .78rem; color: #999; }
    .badge-permiso {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: .75rem;
        font-weight: 500;
        background: #e0f5f1;
        color: #1ab394;
        margin: 2px;
    }
    .modulo-card { border: 1px solid #e7eaec; border-radius: 6px; padding: 12px 14px; margin-bottom: 12px; }
    .badge-permiso-no {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: .75rem;
        font-weight: 500;
        background: #fde8e8;
        color: #e74c3c;
        margin: 2px;
    }
    .modulo-title { font-size: .85rem; font-weight: 700; color: #555; text-transform: capitalize; margin-bottom: 8px; }
    #panelPermisos { min-height: 200px; }
    .check-group label { font-size: .875rem; cursor: pointer; user-select: none; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="row mb-3 p-2">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0">Roles y Permisos</h4>
                <small class="text-muted">Administra los roles del sistema y sus permisos</small>
            </div>
            @can('roles.crear')
            <button class="btn btn-primary btn-sm" onclick="abrirModalCrear()">
                <i class="ti ti-plus me-1"></i> Nuevo rol
            </button>
            @else
            <button class="btn btn-primary btn-sm" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;">
                <i class="ti ti-plus me-1"></i> Nuevo rol
            </button>
            @endcan
        </div>
    </div>

    {{-- Alertas --}}
    <div id="alerta" class="alert d-none mb-3" role="alert"></div>

    <div class="row">

        {{-- Columna izquierda: Lista de roles --}}
        <div class="col-md-4 col-lg-3">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-light py-2">
                    <span class="fw-semibold text-secondary" style="font-size:.85rem;">Roles del sistema</span>
                </div>
                <div class="card-body p-0">
                    <div id="listaRoles">
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-loader-2" style="font-size:1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna derecha: Permisos del rol seleccionado --}}
        <div class="col-md-8 col-lg-9">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-light py-2">
                    <span id="panelTitulo" class="fw-semibold text-secondary" style="font-size:.85rem;">
                        Selecciona un rol para ver sus permisos
                    </span>
                </div>
                <div class="card-body" id="panelPermisos">
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-shield-lock" style="font-size:3rem; opacity:.3;"></i>
                        <p class="mt-3">Selecciona un rol de la lista para ver sus permisos.</p>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /row -->
</div>

{{-- ── Modal Crear / Editar rol ─────────────────────────────── --}}
<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRolLabel">Nuevo rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Nombre --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Nombre del rol</label>
                    <input type="text" id="inputNombre" class="form-control" placeholder="Ej: Supervisor de Sede">
                    <div class="invalid-feedback" id="errorNombre"></div>
                </div>

                {{-- Permisos agrupados --}}
                <div>
                    <label class="form-label fw-semibold">Permisos</label>
                    <div id="permisosModal">
                        <div class="text-center py-3 text-muted"><i class="ti ti-loader-2"></i> Cargando permisos...</div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardar" onclick="guardarRol()">
                    <i class="ti ti-device-floppy me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Confirmar eliminación ─────────────────────────── --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="ti ti-alert-triangle text-danger" style="font-size:2.5rem;"></i>
                <h5 class="mt-3">¿Eliminar este rol?</h5>
                <p class="text-muted mb-0" id="textoEliminar">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm" id="btnConfirmarEliminar">
                    <i class="ti ti-trash me-1"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
const canEditarRol   = {{ auth()->user()->can('roles.editar')   ? 'true' : 'false' }};
const canEliminarRol = {{ auth()->user()->can('roles.eliminar') ? 'true' : 'false' }};
let rolIdEditar   = null;
let rolIdEliminar = null;
let rolSeleccionado = null;
let todosPermisos = {};   // { modulo: [{id, name, accion}] }

const moduloLabels = {
    sedes:          'Sedes',
    empleados:      'Empleados',
    visitantes:     'Visitantes',
    dispositivos:   'Dispositivos',
    asistencia:     'Registros',
    reportes:       'Resumen Marcación',
    permisos:       'Permisos / Ausencias',
    departamentos:  'Deptos. y Cargos',
    empleadores:    'Empleadores',
    empresa:        'Mi Empresa',
    notificaciones: 'Notificaciones',
    horarios:       'Horarios',
    festivos:       'Festivos',
    roles:          'Roles y Permisos',
};
const moduloOrden = [
    'sedes','empleados','visitantes','dispositivos',
    'asistencia','reportes','permisos',
    'departamentos','empleadores',
    'empresa',
    'notificaciones',
    'horarios','festivos','roles',
];
const moduloSecciones = {
    sedes:          'Administración',
    empleados:      'Administración',
    visitantes:     'Administración',
    dispositivos:   'Administración',
    asistencia:     'Asistencia',
    reportes:       'Asistencia',
    permisos:       'Asistencia',
    departamentos:  'Organización',
    empleadores:    'Organización',
    empresa:        'Empresa',
    notificaciones: 'Comunicación',
    horarios:       'Configuración',
    festivos:       'Configuración',
    roles:          'Configuración',
};
function moduloLabel(key) {
    return moduloLabels[key] || key.charAt(0).toUpperCase() + key.slice(1);
}
function sortedModulos(obj) {
    const sorted = {};
    moduloOrden.forEach(k => { if (obj[k]) sorted[k] = obj[k]; });
    Object.keys(obj).forEach(k => { if (!sorted[k]) sorted[k] = obj[k]; });
    return sorted;
}

// ── Inicialización ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cargarRoles();
    cargarTodosPermisos();
});

// ── Lista de roles ────────────────────────────────────────────
async function cargarRoles() {
    const res = await fetch('/admin/roles/list');
    const roles = await res.json();
    renderListaRoles(roles);
    if (rolSeleccionado) mostrarPermisosRol(rolSeleccionado);
}

function renderListaRoles(roles) {
    const el = document.getElementById('listaRoles');
    if (!roles.length) {
        el.innerHTML = '<p class="text-center text-muted py-3" style="font-size:.85rem;">Sin roles registrados.</p>';
        return;
    }
    el.innerHTML = roles.map(r => `
        <div class="role-item px-3 py-2 d-flex align-items-center justify-content-between ${r.id === rolSeleccionado ? 'active' : ''}"
             id="item-rol-${r.id}" onclick="seleccionarRol(${r.id})">
            <div>
                <div class="role-name">${r.name}</div>
                <div class="role-count">${r.permissions_count} permiso${r.permissions_count !== 1 ? 's' : ''}</div>
            </div>
            <div class="d-flex gap-1 ms-2">
                ${canEditarRol
                    ? `<button class="btn btn-sm btn-link p-0 text-muted" title="Editar" onclick="event.stopPropagation(); abrirModalEditar(${r.id}, '${escHtml(r.name)}')"><i class="ti ti-pencil" style="font-size:1rem;"></i></button>`
                    : `<button class="btn btn-sm btn-link p-0 text-muted" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;"><i class="ti ti-pencil" style="font-size:1rem;"></i></button>`}
                ${canEliminarRol
                    ? `<button class="btn btn-sm btn-link p-0 text-danger" title="Eliminar" onclick="event.stopPropagation(); pedirEliminar(${r.id}, '${escHtml(r.name)}')"><i class="ti ti-trash" style="font-size:1rem;"></i></button>`
                    : `<button class="btn btn-sm btn-link p-0 text-danger" disabled data-bs-toggle="tooltip" title="No tiene permiso" style="pointer-events:auto;cursor:not-allowed;"><i class="ti ti-trash" style="font-size:1rem;"></i></button>`}
            </div>
        </div>
    `).join('');
    // Init tooltips en botones deshabilitados
    document.querySelectorAll('#listaRoles [data-bs-toggle="tooltip"]').forEach(el => {
        bootstrap.Tooltip.getOrCreateInstance(el);
    });
}

async function seleccionarRol(id) {
    rolSeleccionado = id;
    document.querySelectorAll('.role-item').forEach(el => el.classList.remove('active'));
    document.getElementById(`item-rol-${id}`)?.classList.add('active');
    mostrarPermisosRol(id);
}

async function mostrarPermisosRol(id) {
    document.getElementById('panelPermisos').innerHTML = '<div class="text-center py-4"><i class="ti ti-loader-2"></i></div>';
    const res = await fetch(`/admin/roles/${id}`);
    const data = await res.json();

    document.getElementById('panelTitulo').innerHTML =
        `Permisos de <span class="text-success">${escHtml(data.name)}</span>`;

    if (!Object.keys(data.permisos).length) {
        document.getElementById('panelPermisos').innerHTML =
            '<p class="text-muted text-center py-4">Este rol no tiene permisos asignados.</p>';
        return;
    }

    // Permisos que tiene el rol (por nombre)
    const tieneSet = new Set();
    for (const permisos of Object.values(data.permisos)) {
        permisos.forEach(p => tieneSet.add(p.name));
    }

    // Mostrar todos los permisos existentes, verde = tiene, rojo = no tiene
    let html = '<div class="row g-3">';
    for (const [modulo, permisos] of Object.entries(sortedModulos(todosPermisos))) {
        const badges = permisos.map(p => tieneSet.has(p.name)
            ? `<span class="badge-permiso">${p.accion}</span>`
            : `<span class="badge-permiso-no">${p.accion}</span>`
        ).join('');
        html += `
            <div class="col-md-6">
                <div class="modulo-card">
                    <div class="modulo-title">${moduloLabel(modulo)}</div>
                    <div>${badges}</div>
                </div>
            </div>`;
    }
    html += '</div>';
    document.getElementById('panelPermisos').innerHTML = html;
}

// ── Todos los permisos (para el modal) ───────────────────────
async function cargarTodosPermisos() {
    const res = await fetch('/admin/roles/permissions');
    todosPermisos = await res.json();
}

// ── Modal crear ───────────────────────────────────────────────
function abrirModalCrear() {
    rolIdEditar = null;
    document.getElementById('modalRolLabel').textContent = 'Nuevo rol';
    document.getElementById('inputNombre').value = '';
    document.getElementById('inputNombre').classList.remove('is-invalid');
    renderPermisosModal([]);
    new bootstrap.Modal(document.getElementById('modalRol')).show();
}

// ── Modal editar ──────────────────────────────────────────────
async function abrirModalEditar(id, nombre) {
    rolIdEditar = id;
    document.getElementById('modalRolLabel').textContent = 'Editar rol';
    document.getElementById('inputNombre').value = nombre;
    document.getElementById('inputNombre').classList.remove('is-invalid');
    document.getElementById('permisosModal').innerHTML = '<div class="text-center py-3 text-muted"><i class="ti ti-loader-2"></i></div>';
    new bootstrap.Modal(document.getElementById('modalRol')).show();

    const res = await fetch(`/admin/roles/${id}`);
    const data = await res.json();
    const seleccionados = [];
    for (const permisos of Object.values(data.permisos)) {
        seleccionados.push(...permisos.map(p => p.name));
    }
    renderPermisosModal(seleccionados);
}

function renderPermisosModal(seleccionados) {
    let cont = document.getElementById('permisosModal');
    if (!Object.keys(todosPermisos).length) {
        cont.innerHTML = '<p class="text-muted">Cargando...</p>';
        return;
    }
    let html = '';
    let seccionActual = null;
    for (const [modulo, permisos] of Object.entries(sortedModulos(todosPermisos))) {
        const seccion = moduloSecciones[modulo] || '';
        if (seccion && seccion !== seccionActual) {
            seccionActual = seccion;
            html += `<div class="text-uppercase fw-bold small text-muted mt-3 mb-1" style="letter-spacing:.08em;font-size:.72rem;">${seccion}</div>`;
        }
        const todos = permisos.map(p => p.name);
        html += `
        <div class="modulo-card mb-2" data-modulo="${modulo}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="modulo-title mb-0">${moduloLabel(modulo)}</div>
                <button type="button" class="btn btn-link btn-sm p-0 text-success btn-toggle-modulo"
                    data-modulo="${modulo}">
                    <span id="lbl-${modulo}">Marcar todos</span>
                </button>
            </div>
            <div class="check-group d-flex flex-wrap gap-3">
                ${permisos.map(p => `
                    <label>
                        <input type="checkbox" class="form-check-input me-1 perm-check"
                            name="permissions[]" value="${p.name}" data-modulo="${modulo}"
                            ${seleccionados.includes(p.name) ? 'checked' : ''}>
                        ${p.accion}
                    </label>`).join('')}
            </div>
        </div>`;
    }
    // Reemplazar el nodo para limpiar listeners anteriores
    const nuevo = cont.cloneNode(false);
    cont.parentNode.replaceChild(nuevo, cont);
    cont = nuevo;
    cont.innerHTML = html;

    // Delegación de eventos (un solo listener por render)
    cont.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-toggle-modulo');
        if (btn) toggleModulo(btn.dataset.modulo);
    });
    cont.addEventListener('change', function(e) {
        if (e.target.classList.contains('perm-check')) {
            actualizarLabelModulo(e.target.dataset.modulo);
        }
    });

    // Actualizar etiquetas iniciales
    for (const modulo of Object.keys(todosPermisos)) {
        actualizarLabelModulo(modulo);
    }
}

function toggleModulo(modulo) {
    const checks = document.querySelectorAll(`input.perm-check[data-modulo="${modulo}"]`);
    const todosActivos = [...checks].every(ch => ch.checked);
    checks.forEach(ch => { ch.checked = !todosActivos; });
    actualizarLabelModulo(modulo);
}

function actualizarLabelModulo(modulo) {
    const checks = document.querySelectorAll(`input.perm-check[data-modulo="${modulo}"]`);
    const todos = [...checks].length > 0 && [...checks].every(ch => ch.checked);
    const lbl = document.getElementById(`lbl-${modulo}`);
    if (lbl) lbl.textContent = todos ? 'Quitar todos' : 'Marcar todos';
}

// ── Guardar rol ───────────────────────────────────────────────
async function guardarRol() {
    const nombre = document.getElementById('inputNombre').value.trim();
    const checks = document.querySelectorAll('input.perm-check:checked');
    const permissions = [...checks].map(c => c.value);

    document.getElementById('inputNombre').classList.remove('is-invalid');

    if (!nombre) {
        document.getElementById('inputNombre').classList.add('is-invalid');
        document.getElementById('errorNombre').textContent = 'El nombre es obligatorio.';
        return;
    }

    const url    = rolIdEditar ? `/admin/roles/${rolIdEditar}` : '/admin/roles';
    const method = rolIdEditar ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ name: nombre, permissions }),
    });
    const data = await res.json();

    if (!res.ok) {
        if (data.errors?.name) {
            document.getElementById('inputNombre').classList.add('is-invalid');
            document.getElementById('errorNombre').textContent = data.errors.name[0];
        } else {
            mostrarAlerta('danger', data.message || 'Error al guardar.');
        }
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('modalRol'))?.hide();
    mostrarAlerta('success', data.message);
    const nuevoId = data.id ?? rolIdEditar;
    await cargarRoles();
    if (nuevoId) seleccionarRol(nuevoId);
}

// ── Eliminar rol ──────────────────────────────────────────────
function pedirEliminar(id, nombre) {
    rolIdEliminar = id;
    document.getElementById('textoEliminar').textContent =
        `¿Deseas eliminar el rol "${nombre}"? Esta acción no se puede deshacer.`;
    document.getElementById('btnConfirmarEliminar').onclick = confirmarEliminar;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
}

async function confirmarEliminar() {
    const res = await fetch(`/admin/roles/${rolIdEliminar}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF },
    });
    const data = await res.json();

    bootstrap.Modal.getInstance(document.getElementById('modalEliminar'))?.hide();

    if (!res.ok) {
        mostrarAlerta('danger', data.message);
        return;
    }

    if (rolSeleccionado === rolIdEliminar) {
        rolSeleccionado = null;
        document.getElementById('panelTitulo').textContent = 'Selecciona un rol para ver sus permisos';
        document.getElementById('panelPermisos').innerHTML =
            '<div class="text-center py-5 text-muted"><i class="ti ti-shield-lock" style="font-size:3rem;opacity:.3;"></i><p class="mt-3">Selecciona un rol de la lista para ver sus permisos.</p></div>';
    }

    mostrarAlerta('success', data.message);
    cargarRoles();
}

// ── Helpers ───────────────────────────────────────────────────
function mostrarAlerta(tipo, msg) {
    const el = document.getElementById('alerta');
    el.className = `alert alert-${tipo} mb-3`;
    el.textContent = msg;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 4000);
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
