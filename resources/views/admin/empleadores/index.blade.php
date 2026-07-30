@extends('layouts.admin')
@section('title', 'Empleadores')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-briefcase me-2 text-primary"></i>Empleadores</h4>
                    <p class="text-muted mb-0">Gestión de empleadores registrados</p>
                </div>
                <button class="btn btn-primary btn-sm" onclick="abrirModal()">
                    <i class="ti ti-plus me-1"></i> Nuevo empleador
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="empleadoresTbody">
                                @forelse($empleadores as $e)
                                <tr id="row-{{ $e->id }}">
                                    <td class="text-muted small">{{ $e->id }}</td>
                                    <td><strong>{{ $e->nombre }}</strong></td>
                                    <td class="text-muted small">{{ $e->descripcion ?? '—' }}</td>
                                    <td>
                                        @if($e->is_active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                            onclick="abrirModal({{ $e->id }}, '{{ addslashes($e->nombre) }}', '{{ addslashes($e->descripcion ?? '') }}', {{ $e->is_active ? 'true' : 'false' }})"
                                            title="Editar">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2 ms-1"
                                            onclick="eliminar({{ $e->id }}, '{{ addslashes($e->nombre) }}')"
                                            title="Eliminar">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay empleadores registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal crear/editar --}}
<div class="modal fade" id="modalEmpleador" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="ti ti-briefcase me-2"></i>Nuevo empleador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlert" class="d-none mb-3"></div>
                <input type="hidden" id="editId">
                <div class="mb-3">
                    <label class="form-label form-label-sm fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="inputNombre" placeholder="Ej: Empresa Contratista S.A.S" maxlength="150">
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm fw-semibold">Descripción</label>
                    <input type="text" class="form-control form-control-sm" id="inputDescripcion" placeholder="Descripción breve (opcional)" maxlength="255">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="inputActivo" checked>
                    <label class="form-check-label form-label-sm" for="inputActivo">Activo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnGuardar" onclick="guardar()">
                    <i class="ti ti-check me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
let modalBS = null;

document.addEventListener('DOMContentLoaded', () => {
    modalBS = new bootstrap.Modal(document.getElementById('modalEmpleador'));
});

function abrirModal(id = null, nombre = '', descripcion = '', activo = true) {
    document.getElementById('editId').value        = id ?? '';
    document.getElementById('inputNombre').value   = nombre;
    document.getElementById('inputDescripcion').value = descripcion;
    document.getElementById('inputActivo').checked = activo;
    document.getElementById('modalAlert').className = 'd-none mb-3';
    document.getElementById('modalTitle').innerHTML = id
        ? '<i class="ti ti-edit me-2"></i>Editar empleador'
        : '<i class="ti ti-briefcase me-2"></i>Nuevo empleador';
    modalBS.show();
    setTimeout(() => document.getElementById('inputNombre').focus(), 300);
}

async function guardar() {
    const id          = document.getElementById('editId').value;
    const nombre      = document.getElementById('inputNombre').value.trim();
    const descripcion = document.getElementById('inputDescripcion').value.trim();
    const isActive    = document.getElementById('inputActivo').checked;
    const alertEl     = document.getElementById('modalAlert');
    const btn         = document.getElementById('btnGuardar');

    if (!nombre) {
        alertEl.className = 'alert alert-warning mb-3';
        alertEl.textContent = 'El nombre es obligatorio.';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    const url    = id ? `/admin/empleadores/${id}` : '/admin/empleadores';
    const method = id ? 'PUT' : 'POST';

    try {
        const res  = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ nombre, descripcion: descripcion || null, is_active: isActive }),
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Error al guardar.');
            alertEl.className = 'alert alert-danger mb-3';
            alertEl.textContent = msg;
            return;
        }

        modalBS.hide();
        window.location.reload();
    } catch {
        alertEl.className = 'alert alert-danger mb-3';
        alertEl.textContent = 'Error de conexión.';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Guardar';
    }
}

async function eliminar(id, nombre) {
    const result = await Swal.fire({
        title: '¿Desactivar empleador?',
        html: `<span class="text-muted">Se desactivará <strong>${nombre}</strong>. Podrás reactivarlo editándolo.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
    });
    if (!result.isConfirmed) return;

    const res  = await fetch(`/admin/empleadores/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    });
    const data = await res.json();

    if (data.success) {
        Swal.fire({ icon: 'success', title: data.message, timer: 1800, showConfirmButton: false })
            .then(() => window.location.reload());
    }
}
</script>
@endpush
