@extends('layouts.admin')
@section('title', 'Configuración de Tablas Tenant')

@push('styles')
<style>
.form-switch .form-check-input { width: 2.5em; height: 1.3em; cursor: pointer; }
.table > tbody > tr > td { vertical-align: middle; }
.badge-central   { background: #dc3545; color: #fff; }
.badge-estructura{ background: #0d6efd; color: #fff; }
.badge-datos     { background: #198754; color: #fff; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-table me-2 text-primary"></i>Configuración de Tablas</h4>
                    <p class="text-muted mb-0">Define qué tablas pertenecen al tenant y cómo se incluyen en el SQL</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.tenants.descargar-sql') }}" class="btn btn-success">
                        <i class="ti ti-download me-1"></i> Descargar SQL
                    </a>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="ti ti-plus me-1"></i> Agregar Tabla
                    </button>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Instrucciones --}}
    <div class="alert alert-info mb-3">
        <strong><i class="ti ti-info-circle me-1"></i>Instrucciones:</strong>
        <ul class="mb-0 mt-1">
            <li><strong>BD Central:</strong> Tabla que permanece SOLO en la BD central (NO se incluye en el SQL para tenants)</li>
            <li><strong>Estructura:</strong> Se crea la tabla (<code>CREATE TABLE</code>) en el SQL del tenant</li>
            <li><strong>Datos:</strong> Se copian los datos (<code>INSERT</code>) de la BD central al SQL del tenant</li>
            <li><strong>Activo:</strong> Si está desactivado, la tabla se ignora completamente</li>
        </ul>
    </div>

    {{-- Filtro búsqueda --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar tabla..." oninput="filterTablas()">
        </div>
        <div class="col-md-8 d-flex align-items-center gap-2">
            <span class="badge badge-central px-2 py-1">BD Central</span>
            <span class="badge badge-estructura px-2 py-1">Estructura</span>
            <span class="badge badge-datos px-2 py-1">Estructura + Datos</span>
            <span class="ms-auto text-muted small" id="contadorTablas"></span>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablasTable">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:180px">Tabla</th>
                            <th class="text-center" style="width:110px">BD Central</th>
                            <th class="text-center" style="width:110px">Estructura</th>
                            <th class="text-center" style="width:110px">Datos</th>
                            <th class="text-center" style="width:90px">Activo</th>
                            <th style="width:80px">Orden</th>
                            <th>Descripción</th>
                            <th style="width:70px"></th>
                        </tr>
                    </thead>
                    <tbody id="tablasTbody">
                        <tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal agregar tabla --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-plus me-2"></i>Agregar Tabla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre de la Tabla <span class="text-danger">*</span></label>
                    <input type="text" id="addNombre" class="form-control font-monospace" placeholder="tbl_nombre_tabla" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <input type="text" id="addDescripcion" class="form-control" placeholder="Descripción de la tabla">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Orden</label>
                    <input type="number" id="addOrden" class="form-control" value="100" min="0">
                </div>
                <div class="row">
                    <div class="col-6 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="addBdCentral">
                            <label class="form-check-label">BD Central</label>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="addEstructura" checked>
                            <label class="form-check-label">Estructura</label>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="addDatos">
                            <label class="form-check-label">Datos</label>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="addActivo" checked>
                            <label class="form-check-label">Activo</label>
                        </div>
                    </div>
                </div>
                <div id="addError" class="alert alert-danger py-2 mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveAdd()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
let tablasData = [];

async function loadTablas() {
    try {
        const res  = await fetch('/api/tenant-tablas', { headers: { 'Authorization': `Bearer ${token}` } });
        const data = await res.json();

        if (!res.ok) {
            document.getElementById('tablasTbody').innerHTML =
                `<tr><td colspan="8" class="text-center text-danger py-4">${data.message ?? 'Error al cargar'}</td></tr>`;
            return;
        }

        tablasData = data.data ?? [];
        renderTablas(tablasData);
    } catch(e) {
        document.getElementById('tablasTbody').innerHTML =
            '<tr><td colspan="8" class="text-center text-danger py-4">Error de conexión</td></tr>';
    }
}

function renderTablas(tablas) {
    document.getElementById('contadorTablas').textContent = tablas.length + ' tablas';

    if (tablas.length === 0) {
        document.getElementById('tablasTbody').innerHTML =
            '<tr><td colspan="8" class="text-center text-muted py-4">Sin tablas configuradas. Agrega la primera.</td></tr>';
        return;
    }

    document.getElementById('tablasTbody').innerHTML = tablas.map(t => {
        let tipoBadge = '';
        if (t.es_bd_central) {
            tipoBadge = '<span class="badge badge-central">Central</span>';
        } else if (t.copiar_datos) {
            tipoBadge = '<span class="badge badge-datos">Est+Datos</span>';
        } else if (t.copiar_estructura) {
            tipoBadge = '<span class="badge badge-estructura">Estructura</span>';
        }

        return `
        <tr data-nombre="${t.nombre_tabla.toLowerCase()}" data-desc="${(t.descripcion ?? '').toLowerCase()}">
            <td>
                <code class="text-primary">${t.nombre_tabla}</code>
                ${tipoBadge ? '<br><span class="mt-1 d-inline-block">' + tipoBadge + '</span>' : ''}
            </td>
            <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input" type="checkbox" ${t.es_bd_central ? 'checked' : ''}
                        onchange="updateField(${t.id}, 'es_bd_central', this.checked)">
                </div>
            </td>
            <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input" type="checkbox" ${t.copiar_estructura ? 'checked' : ''}
                        onchange="updateField(${t.id}, 'copiar_estructura', this.checked)">
                </div>
            </td>
            <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input" type="checkbox" ${t.copiar_datos ? 'checked' : ''}
                        onchange="updateField(${t.id}, 'copiar_datos', this.checked)">
                </div>
            </td>
            <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input" type="checkbox" ${t.activo ? 'checked' : ''}
                        onchange="updateField(${t.id}, 'activo', this.checked)">
                </div>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" value="${t.orden ?? 0}" min="0"
                    style="width:70px"
                    onchange="updateField(${t.id}, 'orden', parseInt(this.value) || 0)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" value="${t.descripcion ?? ''}"
                    placeholder="Descripción"
                    onchange="updateField(${t.id}, 'descripcion', this.value)">
            </td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTabla(${t.id}, '${t.nombre_tabla}')"
                    title="Eliminar">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

function filterTablas() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    if (!q) { renderTablas(tablasData); return; }
    const filtered = tablasData.filter(t =>
        t.nombre_tabla.toLowerCase().includes(q) ||
        (t.descripcion ?? '').toLowerCase().includes(q)
    );
    renderTablas(filtered);
}

let updateTimers = {};

async function updateField(id, field, value) {
    clearTimeout(updateTimers[id + field]);
    updateTimers[id + field] = setTimeout(async () => {
        try {
            const res = await fetch(`/api/tenant-tablas/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ [field]: value }),
            });
            if (res.ok) {
                // Actualizar dato local y re-renderizar badges sin recargar todo
                const idx = tablasData.findIndex(t => t.id === id);
                if (idx >= 0) {
                    tablasData[idx][field] = value;
                    // Solo refrescar badge del row
                    const row = document.querySelector(`[data-nombre="${tablasData[idx].nombre_tabla.toLowerCase()}"]`);
                    if (row) {
                        const t    = tablasData[idx];
                        let badge  = '';
                        if (t.es_bd_central) badge = '<span class="badge badge-central">Central</span>';
                        else if (t.copiar_datos) badge = '<span class="badge badge-datos">Est+Datos</span>';
                        else if (t.copiar_estructura) badge = '<span class="badge badge-estructura">Estructura</span>';
                        row.querySelector('td:first-child').innerHTML =
                            `<code class="text-primary">${t.nombre_tabla}</code>` +
                            (badge ? `<br><span class="mt-1 d-inline-block">${badge}</span>` : '');
                    }
                }
            } else {
                console.error('Error actualizando campo');
            }
        } catch(e) { console.error(e); }
    }, 400);
}

async function deleteTabla(id, nombre) {
    const result = await Swal.fire({
        title: '¿Eliminar tabla?',
        html: `Se eliminará <code>${nombre}</code> de la configuración.<br><small class="text-muted">Esto no elimina la tabla real de la BD.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/api/tenant-tablas/${id}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}` },
        });
        if (res.ok) {
            tablasData = tablasData.filter(t => t.id !== id);
            renderTablas(tablasData);
        }
    } catch(e) { console.error(e); }
}

function openAddModal() {
    document.getElementById('addNombre').value     = '';
    document.getElementById('addDescripcion').value = '';
    document.getElementById('addOrden').value      = '100';
    document.getElementById('addBdCentral').checked = false;
    document.getElementById('addEstructura').checked = true;
    document.getElementById('addDatos').checked    = false;
    document.getElementById('addActivo').checked   = true;
    document.getElementById('addError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

async function saveAdd() {
    const nombre = document.getElementById('addNombre').value.trim();
    if (!nombre) {
        document.getElementById('addError').textContent = 'El nombre de la tabla es obligatorio.';
        document.getElementById('addError').classList.remove('d-none');
        return;
    }

    const payload = {
        nombre_tabla:      nombre,
        descripcion:       document.getElementById('addDescripcion').value.trim() || null,
        orden:             parseInt(document.getElementById('addOrden').value) || 100,
        es_bd_central:     document.getElementById('addBdCentral').checked,
        copiar_estructura: document.getElementById('addEstructura').checked,
        copiar_datos:      document.getElementById('addDatos').checked,
        activo:            document.getElementById('addActivo').checked,
    };

    document.getElementById('addError').classList.add('d-none');

    try {
        const res  = await fetch('/api/tenant-tablas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
            await loadTablas();
        } else {
            const msgs = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message ?? 'Error al guardar');
            document.getElementById('addError').textContent = msgs;
            document.getElementById('addError').classList.remove('d-none');
        }
    } catch(e) {
        document.getElementById('addError').textContent = 'Error de conexión: ' + e.message;
        document.getElementById('addError').classList.remove('d-none');
    }
}

document.addEventListener('DOMContentLoaded', loadTablas);
</script>
@endpush
