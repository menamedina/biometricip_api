@extends('layouts.admin')
@section('title', 'Departamentos y Cargos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <h4 class="mb-1"><i class="fa-solid fa-sitemap me-2 text-primary"></i>Departamentos y Cargos</h4>
            <p class="text-muted mb-0">Estructura organizacional de la empresa</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Departamentos --}}
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header d-block">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0"><i class="fa-solid fa-building-columns me-1"></i> Departamentos</h5>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary" onclick="openImportModal('depto')"
                                title="Importar CSV">
                                <i class="fa-solid fa-file-import"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="openDeptoModal()">
                                <i class="fa-solid fa-plus me-1"></i> Nuevo
                            </button>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('admin.departamentos.index') }}" id="formSearchDepto">
                        <input type="hidden" name="search_cargo" value="{{ $searchCargo }}">
                        <div class="autocomplete-wrap position-relative">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search_depto" id="searchDepto"
                                    class="form-control"
                                    placeholder="Buscar departamento..."
                                    value="{{ $searchDepto }}"
                                    autocomplete="off"
                                    oninput="acFilter('searchDepto','acListDepto')"
                                    onfocus="acFilter('searchDepto','acListDepto')"
                                    onblur="setTimeout(()=>acHide('acListDepto'),150)"
                                    onkeydown="acKey(event,'acListDepto','formSearchDepto')">
                                @if($searchDepto)
                                    <a href="{{ route('admin.departamentos.index', ['search_cargo' => $searchCargo]) }}"
                                        class="btn btn-outline-secondary btn-sm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @else
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                @endif
                            </div>
                            <ul id="acListDepto" class="ac-dropdown list-unstyled mb-0" style="display:none;"></ul>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deptos as $d)
                            <tr>
                                <td><strong>{{ $d->nombre }}</strong></td>
                                <td><small class="text-muted">{{ $d->descripcion ?: '—' }}</small></td>
                                <td>
                                    <span class="badge {{ $d->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $d->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                        onclick='openDeptoModal({{ json_encode($d) }})'>
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST"
                                        action="{{ route('admin.departamentos.destroy', $d->id) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este departamento?')">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="search_depto" value="{{ $searchDepto }}">
                                        <input type="hidden" name="search_cargo" value="{{ $searchCargo }}">
                                        <button class="btn btn-sm btn-outline-danger" {{ auth()->user()->role === 'supervisor' ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    {{ $searchDepto ? 'Sin resultados para "'.$searchDepto.'"' : 'Sin departamentos' }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cargos --}}
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header d-block">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0"><i class="fa-solid fa-user-tie me-1"></i> Cargos</h5>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary" onclick="openImportModal('cargo')"
                                title="Importar CSV">
                                <i class="fa-solid fa-file-import"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="openCargoModal()">
                                <i class="fa-solid fa-plus me-1"></i> Nuevo
                            </button>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('admin.departamentos.index') }}" id="formSearchCargo">
                        <input type="hidden" name="search_depto" value="{{ $searchDepto }}">
                        <div class="autocomplete-wrap position-relative">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search_cargo" id="searchCargo"
                                    class="form-control"
                                    placeholder="Buscar cargo..."
                                    value="{{ $searchCargo }}"
                                    autocomplete="off"
                                    oninput="acFilter('searchCargo','acListCargo')"
                                    onfocus="acFilter('searchCargo','acListCargo')"
                                    onblur="setTimeout(()=>acHide('acListCargo'),150)"
                                    onkeydown="acKey(event,'acListCargo','formSearchCargo')">
                                @if($searchCargo)
                                    <a href="{{ route('admin.departamentos.index', ['search_depto' => $searchDepto]) }}"
                                        class="btn btn-outline-secondary btn-sm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @else
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                @endif
                            </div>
                            <ul id="acListCargo" class="ac-dropdown list-unstyled mb-0" style="display:none;"></ul>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargos as $c)
                            <tr>
                                <td><strong>{{ $c->nombre }}</strong></td>
                                <td><small class="text-muted">{{ $c->descripcion ?: '—' }}</small></td>
                                <td>
                                    <span class="badge {{ $c->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $c->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                        onclick='openCargoModal({{ json_encode($c) }})'>
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST"
                                        action="{{ route('admin.cargos.destroy', $c->id) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este cargo?')">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="search_depto" value="{{ $searchDepto }}">
                                        <input type="hidden" name="search_cargo" value="{{ $searchCargo }}">
                                        <button class="btn btn-sm btn-outline-danger" {{ auth()->user()->role === 'supervisor' ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    {{ $searchCargo ? 'Sin resultados para "'.$searchCargo.'"' : 'Sin cargos' }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Import --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalTitle">Importar CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="search_depto" value="{{ $searchDepto }}">
                <input type="hidden" name="search_cargo" value="{{ $searchCargo }}">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        El archivo debe tener las columnas: <code>nombre</code>, <code>descripcion</code>, <code>is_active</code>.<br>
                        La primera fila (encabezado) se omite. <code>is_active</code> es opcional (1 = activo, 0 = inactivo).
                    </p>
                    <div class="mb-3">
                        <a id="importTemplateLink" href="#" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-download me-1"></i> Descargar plantilla Excel
                        </a>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Archivo Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-file-import me-1"></i> Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Departamento --}}
<div class="modal fade" id="deptoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptoModalTitle">Nuevo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deptoForm" method="POST">
                @csrf
                <span id="deptoMethod"></span>
                <input type="hidden" name="search_depto" value="{{ $searchDepto }}">
                <input type="hidden" name="search_cargo" value="{{ $searchCargo }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="deptoNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="deptoDescripcion" class="form-control">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="deptoActivo" value="1" checked>
                        <label class="form-check-label">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Cargo --}}
<div class="modal fade" id="cargoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cargoModalTitle">Nuevo Cargo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cargoForm" method="POST">
                @csrf
                <span id="cargoMethod"></span>
                <input type="hidden" name="search_depto" value="{{ $searchDepto }}">
                <input type="hidden" name="search_cargo" value="{{ $searchCargo }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="cargoNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="cargoDescripcion" class="form-control">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="cargoActivo" value="1" checked>
                        <label class="form-check-label">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ac-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 .375rem .375rem;
    max-height: 220px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
.ac-dropdown li {
    padding: .45rem .75rem;
    cursor: pointer;
    font-size: .875rem;
    border-bottom: 1px solid #f1f1f1;
}
.ac-dropdown li:last-child { border-bottom: none; }
.ac-dropdown li:hover,
.ac-dropdown li.ac-active { background: #e9f0ff; }
.ac-dropdown li mark {
    background: transparent;
    color: #0d6efd;
    font-weight: 600;
    padding: 0;
}
</style>
@endpush

@push('scripts')
<script>
const deptoNames = @json($allDeptoNames);
const cargoNames = @json($allCargoNames);

const acSources = {
    searchDepto: deptoNames,
    searchCargo: cargoNames,
};

let acCursor = { acListDepto: -1, acListCargo: -1 };

function acFilter(inputId, listId) {
    const input   = document.getElementById(inputId);
    const list    = document.getElementById(listId);
    const query   = input.value.trim().toLowerCase();
    const sources = acSources[inputId] || [];

    acCursor[listId] = -1;

    const matches = query
        ? sources.filter(n => n.toLowerCase().includes(query))
        : sources;

    if (!matches.length) { acHide(listId); return; }

    list.innerHTML = matches.map(n => {
        const highlighted = query
            ? n.replace(new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'), '<mark>$1</mark>')
            : n;
        return `<li onclick="acSelect('${inputId}','${listId}',this)">${highlighted}</li>`;
    }).join('');

    list.style.display = 'block';
}

function acSelect(inputId, listId, li) {
    const input = document.getElementById(inputId);
    input.value = li.textContent;
    acHide(listId);
    input.closest('form').submit();
}

function acHide(listId) {
    const list = document.getElementById(listId);
    list.style.display = 'none';
    acCursor[listId] = -1;
}

function acKey(e, listId, formId) {
    const list  = document.getElementById(listId);
    const items = list.querySelectorAll('li');
    if (!items.length || list.style.display === 'none') return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        acCursor[listId] = Math.min(acCursor[listId] + 1, items.length - 1);
        acHighlight(listId, items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        acCursor[listId] = Math.max(acCursor[listId] - 1, -1);
        acHighlight(listId, items);
    } else if (e.key === 'Enter' && acCursor[listId] >= 0) {
        e.preventDefault();
        items[acCursor[listId]].click();
    } else if (e.key === 'Escape') {
        acHide(listId);
    }
}

function acHighlight(listId, items) {
    items.forEach((li, i) => li.classList.toggle('ac-active', i === acCursor[listId]));
}

// ── Import ────────────────────────────────────────────────────────────────────
const importDeptoUrl    = "{{ route('admin.departamentos.import') }}";
const importCargoUrl    = "{{ route('admin.cargos.import') }}";
const templateDeptoUrl  = "{{ route('admin.departamentos.template') }}";
const templateCargoUrl  = "{{ route('admin.cargos.template') }}";

function openImportModal(type) {
    const isDepto = type === 'depto';
    document.getElementById('importModalTitle').textContent = isDepto ? 'Importar Departamentos' : 'Importar Cargos';
    document.getElementById('importForm').action            = isDepto ? importDeptoUrl : importCargoUrl;
    document.getElementById('importTemplateLink').href      = isDepto ? templateDeptoUrl : templateCargoUrl;
    document.querySelector('#importForm input[type=file]').value = '';
    new bootstrap.Modal(document.getElementById('importModal')).show();
}

// ── Modales ───────────────────────────────────────────────────────────────────
const storeDeptoUrl  = "{{ route('admin.departamentos.store') }}";
const updateDeptoUrl = (id) => `{{ url('admin/departamentos') }}/${id}`;
const storeCargoUrl  = "{{ route('admin.cargos.store') }}";
const updateCargoUrl = (id) => `{{ url('admin/cargos') }}/${id}`;

function openDeptoModal(data = null) {
    const form = document.getElementById('deptoForm');
    document.getElementById('deptoNombre').value      = data?.nombre || '';
    document.getElementById('deptoDescripcion').value = data?.descripcion || '';
    document.getElementById('deptoActivo').checked    = data ? !!data.is_active : true;
    document.getElementById('deptoModalTitle').textContent = data ? 'Editar Departamento' : 'Nuevo Departamento';
    form.action = data ? updateDeptoUrl(data.id) : storeDeptoUrl;
    document.getElementById('deptoMethod').innerHTML  = data ? '<input type="hidden" name="_method" value="PUT">' : '';
    const modal = new bootstrap.Modal(document.getElementById('deptoModal'));
    document.getElementById('deptoModal').addEventListener('shown.bs.modal', () => {
        document.getElementById('deptoNombre').focus();
    }, { once: true });
    modal.show();
}

function openCargoModal(data = null) {
    const form = document.getElementById('cargoForm');
    document.getElementById('cargoNombre').value      = data?.nombre || '';
    document.getElementById('cargoDescripcion').value = data?.descripcion || '';
    document.getElementById('cargoActivo').checked    = data ? !!data.is_active : true;
    document.getElementById('cargoModalTitle').textContent = data ? 'Editar Cargo' : 'Nuevo Cargo';
    form.action = data ? updateCargoUrl(data.id) : storeCargoUrl;
    document.getElementById('cargoMethod').innerHTML  = data ? '<input type="hidden" name="_method" value="PUT">' : '';
    const modal = new bootstrap.Modal(document.getElementById('cargoModal'));
    document.getElementById('cargoModal').addEventListener('shown.bs.modal', () => {
        document.getElementById('cargoNombre').focus();
    }, { once: true });
    modal.show();
}
</script>
@endpush
