@extends('layouts.admin')
@section('title', 'Notificaciones Push')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fa-solid fa-bell me-2 text-primary"></i>Notificaciones Push</h4>
                <p class="text-muted mb-0">Enviar notificaciones a los dispositivos de los empleados</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-send me-1"></i> Enviar Notificación</h5>
                </div>
                <div class="card-body">

                    @if($empresas->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">Empresa <span class="text-danger">*</span></label>
                        <select id="empresaSelect" class="form-select" onchange="onEmpresaChange()">
                            <option value="all">Todas las empresas</option>
                            @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Destinatarios <span class="text-danger">*</span></label>
                        <select id="destinatarios" class="form-select" onchange="onDestinatariosChange()">
                            <option value="all">Todos los empleados</option>
                            @if(!auth()->user()->admin_tenant)
                            <option value="lider">Por líder</option>
                            @endif
                            <option value="selected">Seleccionar empleados</option>
                        </select>
                    </div>

                    @if(!auth()->user()->admin_tenant)
                    <div class="mb-3 d-none" id="liderSelectContainer">
                        <label class="form-label">Líder</label>
                        <select id="liderSelect" class="form-select" onchange="onLiderChange()">
                            <option value="">— Selecciona un líder —</option>
                            @foreach($lideres as $lider)
                            <option value="{{ $lider->id }}">{{ $lider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-3 d-none" id="empleadosSelectContainer">
                        <label class="form-label">Empleados</label>
                        <input type="text" id="empSearch" class="form-control form-control-sm mb-2" placeholder="Buscar empleado..." oninput="filterEmpleados()">
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="empleadosList">
                            @if($empresas->count() === 0)
                                @foreach($empleados as $emp)
                                <div class="form-check emp-item">
                                    <input class="form-check-input emp-check" type="checkbox" value="{{ $emp->id }}" id="emp{{ $emp->id }}">
                                    <label class="form-check-label" for="emp{{ $emp->id }}">
                                        {{ $emp->name }} <small class="text-muted">({{ $emp->email }})</small>
                                    </label>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted small mb-0">Selecciona una empresa primero</p>
                            @endif
                        </div>
                        <div class="mt-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(true)">Seleccionar todos</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">Deseleccionar</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" id="nTitle" class="form-control" placeholder="Ej: Recordatorio importante" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                        <textarea id="nBody" class="form-control" rows="4" placeholder="Escribe el mensaje de la notificación..." maxlength="1000"></textarea>
                        <small class="text-muted"><span id="charCount">0</span>/1000</small>
                    </div>

                    <button class="btn btn-primary w-100" id="btnSend" onclick="sendNotification()">
                        <i class="fa-solid fa-paper-plane me-1"></i> Enviar Notificación
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-device-mobile me-1"></i> Vista previa</h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex align-items-start">
                            <div class="me-2">
                                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary, #4F46E5);display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-bell text-white" style="font-size:16px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong class="small" id="previewApp">BiometricIP</strong>
                                    <span class="text-muted" style="font-size:11px;">ahora</span>
                                </div>
                                <div class="fw-semibold" id="previewTitle" style="font-size:14px;">Título de la notificación</div>
                                <div class="text-muted" id="previewBody" style="font-size:13px;">Mensaje de la notificación</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-info-circle me-1"></i> Información</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="ti ti-check text-success me-1"></i> Las notificaciones llegan cuando la app está en segundo plano o cerrada</li>
                        <li class="mb-2"><i class="ti ti-check text-success me-1"></i> Solo se envían a dispositivos con token registrado</li>
                        <li><i class="ti ti-alert-triangle text-warning me-1"></i> Los tokens inválidos se desactivan automáticamente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial de notificaciones enviadas --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="ti ti-history me-1"></i> Historial de notificaciones</h5>
                    <span class="badge bg-secondary" id="historialCount">{{ $historial->count() }} registro(s)</span>
                </div>
                <div class="card-body">
                    @if($historial->count() > 0)
                    <div class="table-responsive">
                        <table id="historialTable" class="table table-hover mb-0 w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Enviado por</th>
                                    <th>Título</th>
                                    <th>Mensaje</th>
                                    <th>Destinatarios</th>
                                    <th class="text-center">Enviados</th>
                                    <th class="text-center">Exitosos</th>
                                    <th class="text-center">Fallidos</th>
                                    <th class="text-center no-sort"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historial as $h)
                                <tr>
                                    <td data-order="{{ $h->created_at->timestamp }}">
                                        <small>{{ $h->created_at->format('d/m/Y H:i') }}</small>
                                        <br><small class="text-muted">{{ $h->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td><small class="fw-semibold">{{ $h->enviado_por_nombre }}</small></td>
                                    <td class="fw-semibold">{{ $h->titulo }}</td>
                                    <td><small class="text-muted">{{ Str::limit($h->mensaje, 80) }}</small></td>
                                    <td>
                                        @if($h->tipo_destinatario === 'all')
                                            <span class="badge bg-primary">Todos</span>
                                        @elseif($h->tipo_destinatario === 'lider')
                                            <span class="badge bg-info text-dark">Por líder</span>
                                        @else
                                            <span class="badge bg-secondary">Seleccionados ({{ count($h->user_ids ?? []) }})</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $h->total_enviados }}</td>
                                    <td class="text-center">
                                        <span class="text-success fw-semibold">{{ $h->total_exitosos }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($h->total_fallidos > 0)
                                            <span class="text-danger fw-semibold">{{ $h->total_fallidos }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($h->tipo_destinatario !== 'all')
                                        <button class="btn btn-sm btn-outline-primary" onclick="verDestinatarios({{ $h->id }}, '{{ addslashes($h->titulo) }}')" title="Ver destinatarios">
                                            <i class="ti ti-users"></i>
                                        </button>
                                        @else
                                        <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="py-4 text-center text-muted">
                        <i class="ti ti-bell-off" style="font-size:32px;"></i>
                        <p class="mt-2 mb-0">No se han enviado notificaciones aún</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Dispositivos registrados --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="ti ti-device-mobile-check me-1"></i> Dispositivos con Token FCM</h5>
                    <span class="badge bg-primary" id="dispositivosCount">{{ $dispositivos->count() }} dispositivo(s)</span>
                </div>
                <div class="card-body">
                    @if($dispositivos->count() > 0)
                    <div class="table-responsive">
                        <table id="dispositivosTable" class="table table-hover mb-0 w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Empleado</th>
                                    @if(auth()->user()->admin_tenant)
                                    <th>Empresa</th>
                                    @endif
                                    <th>Dispositivo</th>
                                    <th>Token FCM</th>
                                    <th>Registrado</th>
                                    <th>Último acceso</th>
                                    <th>Estado</th>
                                    <th class="text-center no-sort">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dispositivos as $d)
                                @php
                                    $lastAccess   = $d->last_access ? \Carbon\Carbon::parse($d->last_access) : null;
                                    $tokenUpdated = \Carbon\Carbon::parse($d->token_updated_at);
                                    $inactive     = !$d->token_active;
                                    $stale        = $lastAccess ? $lastAccess->diffInDays(now()) > 30 : $tokenUpdated->diffInDays(now()) > 30;
                                @endphp
                                <tr id="token-row-{{ $d->id }}" class="{{ $inactive ? 'table-danger' : ($stale ? 'table-warning' : '') }}">
                                    <td>
                                        <div class="fw-semibold">{{ $d->name }}</div>
                                        <small class="text-muted">{{ $d->email }}</small>
                                    </td>
                                    @if(auth()->user()->admin_tenant)
                                    <td>
                                        <small class="text-secondary">{{ $d->empresa_nombre ?? '—' }}</small>
                                    </td>
                                    @endif
                                    <td>
                                        @if($d->device_type === 'android')
                                            <i class="fa-brands fa-android text-success me-1"></i>
                                        @elseif($d->device_type === 'ios')
                                            <i class="fa-brands fa-apple me-1"></i>
                                        @else
                                            <i class="fa-solid fa-globe me-1"></i>
                                        @endif
                                        {{ ucfirst($d->device_type) }}
                                        @if($d->device_name)
                                            <br><small class="text-muted">{{ $d->device_name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $d->token_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $d->token_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td data-order="{{ \Carbon\Carbon::parse($d->token_registered_at)->timestamp }}">
                                        <small>{{ \Carbon\Carbon::parse($d->token_registered_at)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td data-order="{{ $lastAccess ? $lastAccess->timestamp : 0 }}">
                                        @if($lastAccess)
                                            <small class="{{ $stale ? 'text-warning fw-semibold' : '' }}">
                                                {{ $lastAccess->format('d/m/Y H:i') }}
                                            </small>
                                            <br><small class="text-muted">{{ $lastAccess->diffForHumans() }}</small>
                                        @else
                                            <small class="text-muted">Sin registro</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inactive)
                                            <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Token inactivo</span>
                                        @elseif($stale)
                                            <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Sin actividad (+30d)</span>
                                        @else
                                            <span class="badge bg-success"><i class="ti ti-check me-1"></i>OK</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteToken({{ $d->id }}, '{{ addslashes($d->name) }}')" title="Eliminar token">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="py-4 text-center text-muted">
                        <i class="ti ti-device-mobile-off" style="font-size: 32px;"></i>
                        <p class="mt-2 mb-0">No hay dispositivos con token FCM registrado</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Modal destinatarios --}}
<div class="modal fade" id="modalDestinatarios" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-users me-1"></i> Destinatarios — <span id="modalNotifTitulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="modalDestinatariosBody" class="p-3 text-center text-muted">
                    <div class="spinner-border spinner-border-sm"></div> Cargando...
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
div.dataTables_wrapper div.dataTables_length,
div.dataTables_wrapper div.dataTables_filter { margin-bottom: 8px; }
div.dataTables_wrapper div.dataTables_info,
div.dataTables_wrapper div.dataTables_paginate { margin-top: 8px; }
div.dataTables_wrapper div.dataTables_length label,
div.dataTables_wrapper div.dataTables_filter label { font-size: .875rem; color: #6c757d; }
div.dataTables_wrapper div.dataTables_filter input:focus { border-color: #4F46E5; box-shadow: 0 0 0 .2rem rgba(79,70,229,.15); }
div.dataTables_wrapper div.dataTables_info { font-size: .8rem; color: #6c757d; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const isAdminTenant = {{ auth()->user()->admin_tenant ? 'true' : 'false' }};
const destSelect = document.getElementById('destinatarios');
const empContainer = document.getElementById('empleadosSelectContainer');
const empList = document.getElementById('empleadosList');
const nTitle = document.getElementById('nTitle');
const nBody = document.getElementById('nBody');

nTitle.addEventListener('input', () => {
    document.getElementById('previewTitle').textContent = nTitle.value || 'Título de la notificación';
});

nBody.addEventListener('input', () => {
    document.getElementById('previewBody').textContent = nBody.value || 'Mensaje de la notificación';
    document.getElementById('charCount').textContent = nBody.value.length;
});

function toggleAll(checked) {
    document.querySelectorAll('.emp-check').forEach(cb => cb.checked = checked);
}

function filterEmpleados() {
    const search = document.getElementById('empSearch').value.toLowerCase();
    document.querySelectorAll('.emp-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(search) ? '' : 'none';
    });
}

function onDestinatariosChange() {
    const val = destSelect.value;
    const liderContainer = document.getElementById('liderSelectContainer');

    empContainer.classList.toggle('d-none', val !== 'selected');
    if (liderContainer) liderContainer.classList.toggle('d-none', val !== 'lider');

    if (val === 'selected' && isAdminTenant) {
        loadEmpleados();
    }
    if (val === 'lider') {
        const liderSelect = document.getElementById('liderSelect');
        if (liderSelect && liderSelect.value) onLiderChange();
    }
}

function onLiderChange() {
    const liderId = document.getElementById('liderSelect')?.value;
    if (!liderId) return;
    // Los empleados del líder se filtran al enviar; no precargamos lista
}

function onEmpresaChange() {
    if (destSelect.value === 'selected') {
        loadEmpleados();
    }
}

async function loadEmpleados() {
    const empresaSelect = document.getElementById('empresaSelect');
    const empresaId = empresaSelect ? empresaSelect.value : null;

    if (isAdminTenant && empresaId === 'all') {
        // Cargar de todas las empresas
        empList.innerHTML = '<p class="text-muted small mb-0">Cargando empleados de todas las empresas...</p>';
        try {
            const promises = [...empresaSelect.options]
                .filter(o => o.value && o.value !== 'all')
                .map(o => fetch(`/admin/notificaciones/empleados?empresa_id=${o.value}`, {
                    headers: { 'Accept': 'application/json' },
                }).then(r => r.json()).then(emps => emps.map(e => ({ ...e, empresa: o.text }))));

            const results = await Promise.all(promises);
            const allEmps = results.flat();

            if (allEmps.length === 0) {
                empList.innerHTML = '<p class="text-muted small mb-0">No hay empleados registrados</p>';
                return;
            }

            empList.innerHTML = allEmps.map(emp =>
                `<div class="form-check emp-item">
                    <input class="form-check-input emp-check" type="checkbox" value="${emp.id}" id="emp${emp.id}">
                    <label class="form-check-label" for="emp${emp.id}">
                        ${emp.name} <small class="text-muted">(${emp.email} - ${emp.empresa})</small>
                    </label>
                </div>`
            ).join('');
        } catch (e) {
            empList.innerHTML = '<p class="text-danger small mb-0">Error al cargar empleados</p>';
        }
        return;
    }

    if (isAdminTenant && (!empresaId || empresaId === '')) {
        empList.innerHTML = '<p class="text-muted small mb-0">Selecciona una empresa primero</p>';
        return;
    }

    empList.innerHTML = '<p class="text-muted small mb-0">Cargando empleados...</p>';

    try {
        let url = '/admin/notificaciones/empleados';
        if (empresaId && empresaId !== 'all') url += `?empresa_id=${empresaId}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const empleados = await res.json();

        if (empleados.length === 0) {
            empList.innerHTML = '<p class="text-muted small mb-0">No hay empleados en esta empresa</p>';
            return;
        }

        empList.innerHTML = empleados.map(emp =>
            `<div class="form-check emp-item">
                <input class="form-check-input emp-check" type="checkbox" value="${emp.id}" id="emp${emp.id}">
                <label class="form-check-label" for="emp${emp.id}">
                    ${emp.name} <small class="text-muted">(${emp.email})</small>
                </label>
            </div>`
        ).join('');
    } catch (e) {
        empList.innerHTML = '<p class="text-danger small mb-0">Error al cargar empleados</p>';
    }
}

async function sendNotification() {
    const title = nTitle.value.trim();
    const body = nBody.value.trim();

    if (!title || !body) {
        Swal.fire('Error', 'El título y el mensaje son obligatorios.', 'error');
        return;
    }

    const payload = { title, body };

    if (isAdminTenant) {
        const empresaId = document.getElementById('empresaSelect')?.value;
        if (empresaId && empresaId !== 'all') {
            payload.empresa_id = empresaId;
        }
    }

    if (destSelect.value === 'lider') {
        const liderId = document.getElementById('liderSelect')?.value;
        if (!liderId) {
            Swal.fire('Error', 'Selecciona un líder.', 'error');
            return;
        }
        payload.lider_id = parseInt(liderId);
    } else if (destSelect.value === 'selected') {
        const selected = [...document.querySelectorAll('.emp-check:checked')].map(cb => parseInt(cb.value));
        if (selected.length === 0) {
            Swal.fire('Error', 'Selecciona al menos un empleado.', 'error');
            return;
        }
        payload.user_ids = selected;
    }

    const btn = document.getElementById('btnSend');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

    try {
        const res = await fetch('/admin/notificaciones/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Notificación enviada',
                html: `<b>${data.result.success}</b> enviada(s) correctamente<br><b>${data.result.failure}</b> fallida(s)`,
            });
            nTitle.value = '';
            nBody.value = '';
            document.getElementById('previewTitle').textContent = 'Título de la notificación';
            document.getElementById('previewBody').textContent = 'Mensaje de la notificación';
            document.getElementById('charCount').textContent = '0';
            recargarHistorial();
        } else {
            Swal.fire('Error', data.message || 'Error al enviar la notificación.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Enviar Notificación';
    }
}

// DataTable dispositivos
const isAdminTenantBlade = {{ auth()->user()->admin_tenant ? 'true' : 'false' }};
$(document).ready(function () {
    if ($('#historialTable').length) {
        $('#historialTable').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
            pageLength: 10,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [3, 8] }],
            dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        });
    }

    if ($('#dispositivosTable').length) {
        const columnDefs = [{ orderable: false, targets: isAdminTenantBlade ? 7 : 6 }];

        $('#dispositivosTable').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
            pageLength: 10,
            order: [[isAdminTenantBlade ? 5 : 4, 'desc']],
            columnDefs: columnDefs,
            searching: true,
            paging: true,
            info: true,
            dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        });
    }
});

async function recargarHistorial() {
    try {
        const res  = await fetch('/admin/notificaciones/historial', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        // Destruir DataTable antes de modificar el DOM
        if ($.fn.DataTable.isDataTable('#historialTable')) {
            $('#historialTable').DataTable().destroy();
        }

        const tbody = document.querySelector('#historialTable tbody');
        if (!tbody) return;

        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">No se han enviado notificaciones aún</td></tr>';
        } else {
            tbody.innerHTML = data.map(h => {
                let badge = '';
                if (h.tipo_destinatario === 'all')        badge = '<span class="badge bg-primary">Todos</span>';
                else if (h.tipo_destinatario === 'lider') badge = '<span class="badge bg-info text-dark">Por líder</span>';
                else badge = `<span class="badge bg-secondary">Seleccionados (${h.total_user_ids})</span>`;

                const btnDest = h.tipo_destinatario !== 'all'
                    ? `<button class="btn btn-sm btn-outline-primary" onclick="verDestinatarios(${h.id}, '${h.titulo.replace(/'/g, "\\'")}')"><i class="ti ti-users"></i></button>`
                    : '<span class="text-muted small">—</span>';

                const fallidos = h.total_fallidos > 0
                    ? `<span class="text-danger fw-semibold">${h.total_fallidos}</span>`
                    : '<span class="text-muted">0</span>';

                return `<tr>
                    <td data-order="${h.created_at_ts}">
                        <small>${h.created_at_fmt}</small><br>
                        <small class="text-muted">${h.created_at_human}</small>
                    </td>
                    <td><small class="fw-semibold">${h.enviado_por_nombre}</small></td>
                    <td class="fw-semibold">${h.titulo}</td>
                    <td><small class="text-muted">${h.mensaje_corto}</small></td>
                    <td>${badge}</td>
                    <td class="text-center">${h.total_enviados}</td>
                    <td class="text-center"><span class="text-success fw-semibold">${h.total_exitosos}</span></td>
                    <td class="text-center">${fallidos}</td>
                    <td class="text-center">${btnDest}</td>
                </tr>`;
            }).join('');
        }

        // Re-inicializar DataTable
        $('#historialTable').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
            pageLength: 10,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [3, 8] }],
            dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        });

        const badge = document.getElementById('historialCount');
        if (badge) badge.textContent = `${data.length} registro(s)`;

    } catch (e) {
        console.error('Error al recargar historial', e);
    }
}

async function verDestinatarios(id, titulo) {
    document.getElementById('modalNotifTitulo').textContent = titulo;
    document.getElementById('modalDestinatariosBody').innerHTML =
        '<div class="p-3 text-center text-muted"><div class="spinner-border spinner-border-sm"></div> Cargando...</div>';

    const modal = new bootstrap.Modal(document.getElementById('modalDestinatarios'));
    modal.show();

    try {
        const res  = await fetch(`/admin/notificaciones/historial/${id}/destinatarios`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        if (!data.length) {
            document.getElementById('modalDestinatariosBody').innerHTML =
                '<div class="p-3 text-center text-muted">No hay destinatarios registrados</div>';
            return;
        }

        const rows = data.map(d => `
            <tr>
                <td class="fw-semibold">${d.name}</td>
                <td><small class="text-muted">${d.email}</small></td>
            </tr>`).join('');

        document.getElementById('modalDestinatariosBody').innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Nombre</th><th>Correo</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
    } catch (e) {
        document.getElementById('modalDestinatariosBody').innerHTML =
            '<div class="p-3 text-center text-danger">Error al cargar los destinatarios</div>';
    }
}

async function deleteToken(id, name) {
    const result = await Swal.fire({
        title: 'Eliminar token',
        text: `¿Eliminar el token FCM de ${name}? El dispositivo dejará de recibir notificaciones.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/notificaciones/device-token/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (res.ok) {
            document.getElementById(`token-row-${id}`)?.remove();
            Swal.fire('Eliminado', 'Token eliminado correctamente.', 'success');
        } else {
            Swal.fire('Error', 'No se pudo eliminar el token.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Error de conexión.', 'error');
    }
}
</script>
@endpush
