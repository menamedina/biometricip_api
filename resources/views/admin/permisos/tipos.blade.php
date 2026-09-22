@extends('layouts.admin')
@section('title', 'Tipos de Permiso')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-clipboard-list me-2 text-primary"></i>Tipos de Permiso</h4>
                    <p class="text-muted mb-0">Catalogo de tipos de permiso/ausencia configurables por empresa</p>
                </div>
                <button class="btn btn-primary btn-sm" onclick="abrirModalNuevo()">
                    <i class="fa-solid fa-plus me-1"></i> Nuevo Tipo
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-lg border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0" id="tiposTable" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Nombre</th>
                        <th style="width:140px;" class="text-center">Remunerado</th>
                        <th style="width:100px;" class="text-center">Activo</th>
                        <th style="width:100px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tipos as $t)
                    <tr data-id="{{ $t->id }}">
                        <td class="text-muted">{{ $t->id }}</td>
                        <td>
                            <span class="nombre-display">{{ $t->nombre }}</span>
                            <input type="text" class="form-control form-control-sm nombre-edit d-none"
                                   value="{{ $t->nombre }}" style="max-width:300px;">
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input class="form-check-input input-remunerado" type="checkbox"
                                       {{ $t->es_remunerado ? 'checked' : '' }}
                                       onchange="guardarCambio({{ $t->id }})">
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input class="form-check-input input-activo" type="checkbox"
                                       {{ $t->is_active ? 'checked' : '' }}
                                       onchange="guardarCambio({{ $t->id }})">
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="editarNombre(this)" title="Editar nombre">
                                <i class="ti ti-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3 border-0 shadow-sm">
        <div class="card-body py-2">
            <small class="text-muted">
                <i class="ti ti-info-circle me-1"></i>
                Los tipos de permiso remunerados son aquellos donde el empleado mantiene su salario durante la ausencia. Los cambios se guardan automaticamente al modificar los toggles.
            </small>
        </div>
    </div>
</div>

{{-- Modal Nuevo Tipo --}}
<div class="modal fade" id="nuevoTipoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Tipo de Permiso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nuevoNombre" class="form-control" placeholder="Ej: Cita Medica">
                </div>
                <div class="mb-3">
                    <label class="form-label">Remunerado</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="nuevoRemunerado" checked>
                        <label class="form-check-label" for="nuevoRemunerado">Si, es remunerado</label>
                    </div>
                </div>
                <div id="nuevoError" class="alert alert-danger py-2 mb-0" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="crearTipo()">Crear</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

function abrirModalNuevo() {
    document.getElementById('nuevoNombre').value = '';
    document.getElementById('nuevoRemunerado').checked = true;
    document.getElementById('nuevoError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('nuevoTipoModal')).show();
}

async function crearTipo() {
    const nombre = document.getElementById('nuevoNombre').value.trim();
    const esRemunerado = document.getElementById('nuevoRemunerado').checked;
    const errorDiv = document.getElementById('nuevoError');

    if (!nombre) {
        errorDiv.textContent = 'El nombre es requerido.';
        errorDiv.style.display = 'block';
        return;
    }

    try {
        const res = await fetch('/admin/permisos/tipos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ nombre, es_remunerado: esRemunerado }),
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        bootstrap.Modal.getInstance(document.getElementById('nuevoTipoModal')).hide();
        location.reload();
    } catch(e) {
        errorDiv.textContent = 'Error al crear: ' + e.message;
        errorDiv.style.display = 'block';
    }
}

async function guardarCambio(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const esRemunerado = row.querySelector('.input-remunerado').checked;
    const isActive = row.querySelector('.input-activo').checked;
    const nombre = row.querySelector('.nombre-edit').value.trim() || row.querySelector('.nombre-display').textContent.trim();

    try {
        await fetch(`/admin/permisos/tipos/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ nombre, es_remunerado: esRemunerado, is_active: isActive }),
        });
    } catch(e) {
        alert('Error al guardar: ' + e.message);
    }
}

function editarNombre(btn) {
    const row = btn.closest('tr');
    const display = row.querySelector('.nombre-display');
    const input = row.querySelector('.nombre-edit');
    const id = row.dataset.id;

    if (input.classList.contains('d-none')) {
        // Mostrar input
        input.classList.remove('d-none');
        display.classList.add('d-none');
        input.focus();
        btn.innerHTML = '<i class="ti ti-check"></i>';
        btn.classList.replace('btn-outline-primary', 'btn-outline-success');
    } else {
        // Guardar
        display.textContent = input.value.trim();
        input.classList.add('d-none');
        display.classList.remove('d-none');
        btn.innerHTML = '<i class="ti ti-pencil"></i>';
        btn.classList.replace('btn-outline-success', 'btn-outline-primary');
        guardarCambio(id);
    }
}
</script>
@endpush
