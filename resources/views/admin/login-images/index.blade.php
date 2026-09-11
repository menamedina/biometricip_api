@extends('layouts.admin')
@section('title', 'Imágenes del Login')

@section('content')
<div class="container-fluid">

    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="ti ti-photo me-2 text-primary"></i>Imágenes del Login</h4>
                <p class="text-muted mb-0">Gestiona las imágenes del carrusel en la pantalla de inicio de sesión</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModal()">
                <i class="ti ti-plus me-1"></i> Nueva imagen
            </button>
        </div>
    </div>

    <div id="alerta" class="alert d-none mb-3"></div>

    <div class="row g-3" id="listaImagenes">
        @forelse($images as $img)
        <div class="col-lg-3 col-md-4 col-sm-6" id="card-{{ $img->id }}">
            <div class="card shadow-lg border-0 h-100">
                <div class="position-relative">
                    <a href="{{ asset('storage/' . $img->imagen) }}" target="_blank">
                        <img src="{{ asset('storage/' . $img->imagen) }}" class="card-img-top"
                             style="height:180px;object-fit:cover;cursor:pointer;" alt="{{ $img->titulo }}">
                    </a>
                    @if(!$img->activo)
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">Inactiva</span>
                    @endif
                </div>
                <div class="card-body py-2 px-3">
                    <p class="mb-1 fw-semibold small text-truncate">{{ $img->titulo ?: 'Sin título' }}</p>
                    <span class="text-muted" style="font-size:.75rem;">Orden: {{ $img->orden }}</span>
                </div>
                <div class="card-footer bg-transparent border-0 py-2 px-3 d-flex justify-content-between">
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleActivo({{ $img->id }})" title="Activar/Desactivar">
                            <i class="ti {{ $img->activo ? 'ti-eye' : 'ti-eye-off' }}" id="toggleIcon-{{ $img->id }}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="abrirEditar({{ $img->id }}, '{{ addslashes($img->titulo) }}', {{ $img->orden }}, '{{ asset('storage/' . $img->imagen) }}')" title="Editar">
                            <i class="ti ti-edit"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminar({{ $img->id }})">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12" id="emptyState">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <i class="ti ti-photo-off text-muted" style="font-size:3rem;"></i>
                    <p class="text-muted mt-2 mb-0">No hay imágenes configuradas. El login mostrará un icono por defecto.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- Modal nueva imagen --}}
<div class="modal fade" id="modalImagen" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-upload me-2"></i>Subir imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formImagen" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Imagen <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="inputImagen" name="imagen"
                               accept="image/jpeg,image/png,image/webp" required>
                        <div class="form-text">JPG, PNG o WebP. Máx. 5 MB. Recomendado: 800x600 px o más.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título</label>
                        <input type="text" class="form-control" id="inputTitulo" name="titulo" maxlength="150"
                               placeholder="Ej: Control biométrico en oficina">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Orden</label>
                        <input type="number" class="form-control" id="inputOrden" name="orden" value="0" min="0">
                    </div>

                    {{-- Preview --}}
                    <div id="previewWrap" class="d-none mb-3">
                        <img id="previewImg" class="rounded w-100" style="max-height:200px;object-fit:cover;" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSubir" onclick="subir()">
                    <i class="ti ti-upload me-1" id="btnSubirIcon"></i>
                    <span id="btnSubirText">Subir imagen</span>
                </button>
            </div>
        </div>
    </div>
</div>
{{-- Modal editar imagen --}}
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-edit me-2"></i>Editar imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Imagen actual</label>
                    <div>
                        <img id="editImgPreview" src="" class="rounded w-100" style="max-height:200px;object-fit:cover;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cambiar imagen</label>
                    <input type="file" class="form-control" id="editImagen" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Dejar vacío para mantener la actual</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Título</label>
                    <input type="text" class="form-control" id="editTitulo" maxlength="150">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Orden</label>
                    <input type="number" class="form-control" id="editOrden" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEditar" onclick="guardarEditar()">
                    <i class="ti ti-device-floppy me-1" id="btnEditarIcon"></i>
                    <span id="btnEditarText">Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let modalInstance = null;
let modalEditarInstance = null;

function abrirModal() {
    document.getElementById('formImagen').reset();
    document.getElementById('previewWrap').classList.add('d-none');
    if (!modalInstance) modalInstance = new bootstrap.Modal(document.getElementById('modalImagen'));
    modalInstance.show();
}

// Preview de imagen
document.getElementById('inputImagen').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('previewWrap').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});

async function subir() {
    const btn  = document.getElementById('btnSubir');
    const icon = document.getElementById('btnSubirIcon');
    const text = document.getElementById('btnSubirText');
    const fileInput = document.getElementById('inputImagen');

    if (!fileInput.files.length) {
        mostrarAlerta('warning', 'Selecciona una imagen');
        return;
    }

    btn.disabled = true;
    icon.className = 'spinner-border spinner-border-sm me-1';
    text.textContent = 'Subiendo...';

    try {
        const fd = new FormData();
        fd.append('imagen', fileInput.files[0]);
        fd.append('titulo', document.getElementById('inputTitulo').value);
        fd.append('orden', document.getElementById('inputOrden').value);

        const res = await fetch('/admin/login-images', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: fd,
        });
        const data = await res.json();

        if (!res.ok) {
            mostrarAlerta('danger', data.message);
            return;
        }

        mostrarAlerta('success', data.message);
        modalInstance.hide();

        // Agregar card sin recargar
        const empty = document.getElementById('emptyState');
        if (empty) empty.remove();
        const html = `<div class="col-lg-3 col-md-4 col-sm-6" id="card-${data.image.id}">
            <div class="card shadow-lg border-0 h-100">
                <div class="position-relative">
                    <a href="${data.image.url}" target="_blank">
                        <img src="${data.image.url}" class="card-img-top" style="height:180px;object-fit:cover;cursor:pointer;">
                    </a>
                </div>
                <div class="card-body py-2 px-3">
                    <p class="mb-1 fw-semibold small text-truncate">${data.image.titulo || 'Sin título'}</p>
                    <span class="text-muted" style="font-size:.75rem;">Orden: ${data.image.orden}</span>
                </div>
                <div class="card-footer bg-transparent border-0 py-2 px-3 d-flex justify-content-between">
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleActivo(${data.image.id})" title="Activar/Desactivar">
                            <i class="ti ti-eye" id="toggleIcon-${data.image.id}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="abrirEditar(${data.image.id}, '${(data.image.titulo||'').replace(/'/g,"\\'")}', ${data.image.orden}, '${data.image.url}')" title="Editar">
                            <i class="ti ti-edit"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminar(${data.image.id})">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
        document.getElementById('listaImagenes').insertAdjacentHTML('beforeend', html);
    } catch (e) {
        mostrarAlerta('danger', 'Error: ' + e.message);
    } finally {
        btn.disabled = false;
        icon.className = 'ti ti-upload me-1';
        text.textContent = 'Subir imagen';
    }
}

async function toggleActivo(id) {
    try {
        const res = await fetch(`/admin/login-images/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (!res.ok) { mostrarAlerta('danger', data.message); return; }

        const icon = document.getElementById('toggleIcon-' + id);
        icon.className = data.activo ? 'ti ti-eye' : 'ti ti-eye-off';
        mostrarAlerta('success', data.message);
    } catch (e) {
        mostrarAlerta('danger', 'Error: ' + e.message);
    }
}

async function eliminar(id) {
    if (!confirm('¿Eliminar esta imagen? Esta acción no se puede deshacer.')) return;

    try {
        const res = await fetch(`/admin/login-images/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (!res.ok) { mostrarAlerta('danger', data.message); return; }

        const card = document.getElementById('card-' + id);
        if (card) card.remove();
        mostrarAlerta('success', data.message);
    } catch (e) {
        mostrarAlerta('danger', 'Error: ' + e.message);
    }
}

function abrirEditar(id, titulo, orden, imgUrl) {
    document.getElementById('editId').value = id;
    document.getElementById('editTitulo').value = titulo;
    document.getElementById('editOrden').value = orden;
    document.getElementById('editImgPreview').src = imgUrl;
    document.getElementById('editImagen').value = '';
    if (!modalEditarInstance) modalEditarInstance = new bootstrap.Modal(document.getElementById('modalEditar'));
    modalEditarInstance.show();
}

async function guardarEditar() {
    const btn  = document.getElementById('btnEditar');
    const icon = document.getElementById('btnEditarIcon');
    const text = document.getElementById('btnEditarText');
    const id   = document.getElementById('editId').value;

    btn.disabled = true;
    icon.className = 'spinner-border spinner-border-sm me-1';
    text.textContent = 'Guardando...';

    try {
        const fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('titulo', document.getElementById('editTitulo').value);
        fd.append('orden', document.getElementById('editOrden').value);

        const fileInput = document.getElementById('editImagen');
        if (fileInput.files.length) {
            fd.append('imagen', fileInput.files[0]);
        }

        const res = await fetch(`/admin/login-images/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: fd,
        });
        const data = await res.json();
        if (!res.ok) { mostrarAlerta('danger', data.message); return; }

        mostrarAlerta('success', data.message);
        modalEditarInstance.hide();

        // Actualizar card sin recargar
        const card = document.getElementById('card-' + id);
        if (card) {
            const titulo = document.getElementById('editTitulo').value || 'Sin título';
            const orden  = document.getElementById('editOrden').value;
            card.querySelector('.card-body p').textContent = titulo;
            card.querySelector('.card-body span').textContent = 'Orden: ' + orden;
            // Si cambió imagen, actualizar src
            if (data.url) {
                const img = card.querySelector('.card-img-top');
                const link = card.querySelector('a');
                if (img) img.src = data.url;
                if (link) link.href = data.url;
            }
        }
    } catch (e) {
        mostrarAlerta('danger', 'Error: ' + e.message);
    } finally {
        btn.disabled = false;
        icon.className = 'ti ti-device-floppy me-1';
        text.textContent = 'Guardar';
    }
}

function mostrarAlerta(tipo, msg) {
    const el = document.getElementById('alerta');
    el.className = `alert alert-${tipo} mb-3`;
    el.textContent = msg;
    setTimeout(() => el.classList.add('d-none'), 4000);
}
</script>
@endpush
