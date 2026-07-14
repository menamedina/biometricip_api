@extends('layouts.admin')
@section('title', 'Nuevo Tenant')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="ti ti-database-plus me-2 text-primary"></i>Nuevo Tenant</h4>
                    <p class="text-muted mb-0">Crear empresa y base de datos para un nuevo tenant</p>
                </div>
                <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Alerta informativa --}}
            <div class="alert alert-info mb-4">
                <i class="ti ti-info-circle me-2"></i>
                Al crear un tenant se ejecutan los siguientes procesos automáticamente:
                <ul class="mb-0 mt-1">
                    <li>Creación de la base de datos exclusiva para la empresa</li>
                    <li>Aplicación de la estructura de tablas (migraciones tenant)</li>
                    <li>Carga de datos iniciales y creación del usuario administrador</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-building me-2"></i>Datos de la Empresa</h5>
                </div>
                <div class="card-body">
                    <form id="createForm">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-semibold">Nombre de la Empresa <span class="text-danger">*</span></label>
                                <input type="text" id="nombre" class="form-control" placeholder="Empresa S.A.S." required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">RUC / NIT</label>
                                <input type="text" id="ruc" class="form-control" placeholder="900123456-7">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email Empresa</label>
                                <input type="email" id="email" class="form-control" placeholder="contacto@empresa.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="text" id="telefono" class="form-control" placeholder="+57 300 000 0000">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Plan</label>
                                <select id="plan" class="form-select">
                                    <option value="bronce" selected>Bronce</option>
                                    <option value="plata">Plata</option>
                                    <option value="oro">Oro</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Máx. Usuarios</label>
                                <input type="number" id="maxUsuarios" class="form-control" value="50" min="1">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-server me-2"></i>Base de Datos</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="ti ti-info-circle me-1"></i>
                        Si dejas los campos vacíos se usarán los valores por defecto del servidor
                        (<code>biometricip_tenant_{id}</code> y credenciales del <code>.env</code>).
                    </p>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Nombre de la Base de Datos</label>
                            <input type="text" id="dbName" class="form-control font-monospace"
                                placeholder="biometricip_tenant_N  (auto)"
                                pattern="[a-zA-Z0-9_]+"
                                title="Solo letras, números y guión bajo">
                            <div class="form-text">Solo letras, números y guión bajo. Sin espacios ni caracteres especiales.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Usuario MySQL</label>
                            <input type="text" id="dbUser" class="form-control font-monospace" placeholder="root  (auto)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Contraseña MySQL</label>
                            <div class="input-group">
                                <input type="password" id="dbPass" class="form-control font-monospace" placeholder="••••••  (auto)">
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleDbPass()">
                                    <i class="ti ti-eye" id="dbPassEyeIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-user-shield me-2"></i>Usuario Administrador Inicial</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Nombre del Admin</label>
                            <input type="text" id="adminName" class="form-control" placeholder="Administrador">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Admin <span class="text-danger">*</span></label>
                            <input type="email" id="adminEmail" class="form-control" placeholder="admin@empresa.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="adminPassword" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="ti ti-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Área de errores --}}
            <div id="formError" class="alert alert-danger mt-3 d-none"></div>

            {{-- Spinner de carga --}}
            <div id="loadingArea" class="card mt-3 d-none">
                <div class="card-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="mb-0 fw-semibold">Creando tenant...</p>
                    <p class="text-muted small">Esto puede tardar unos segundos mientras se configura la base de datos.</p>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 mb-4">
                <a href="{{ route('admin.tenants.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
                <button type="button" class="btn btn-primary" onclick="createTenant()">
                    <i class="ti ti-database-plus me-1"></i> Crear Tenant
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');

function togglePassword() {
    const input = document.getElementById('adminPassword');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ti ti-eye-off';
    } else {
        input.type = 'password';
        icon.className = 'ti ti-eye';
    }
}

function toggleDbPass() {
    const input = document.getElementById('dbPass');
    const icon  = document.getElementById('dbPassEyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ti ti-eye-off';
    } else {
        input.type = 'password';
        icon.className = 'ti ti-eye';
    }
}

async function createTenant() {
    const nombre        = document.getElementById('nombre').value.trim();
    const ruc           = document.getElementById('ruc').value.trim();
    const email         = document.getElementById('email').value.trim();
    const telefono      = document.getElementById('telefono').value.trim();
    const plan          = document.getElementById('plan').value;
    const maxUsuarios   = parseInt(document.getElementById('maxUsuarios').value) || 50;
    const dbName        = document.getElementById('dbName').value.trim();
    const dbUser        = document.getElementById('dbUser').value.trim();
    const dbPass        = document.getElementById('dbPass').value;
    const adminName     = document.getElementById('adminName').value.trim();
    const adminEmail    = document.getElementById('adminEmail').value.trim();
    const adminPassword = document.getElementById('adminPassword').value;

    const errorEl = document.getElementById('formError');
    errorEl.classList.add('d-none');

    if (!nombre)        { showError('El nombre de la empresa es obligatorio.'); return; }
    if (!adminEmail)    { showError('El email del administrador es obligatorio.'); return; }
    if (!adminPassword) { showError('La contraseña del administrador es obligatoria.'); return; }
    if (adminPassword.length < 6) { showError('La contraseña debe tener al menos 6 caracteres.'); return; }

    if (dbName && !/^[a-zA-Z0-9_]+$/.test(dbName)) {
        showError('El nombre de la BD solo puede contener letras, números y guión bajo.');
        return;
    }

    const payload = {
        nombre,
        ruc:            ruc      || null,
        email:          email    || null,
        telefono:       telefono || null,
        plan,
        max_usuarios:   maxUsuarios,
        db_name:        dbName   || null,
        db_user:        dbUser   || null,
        db_pass:        dbPass   || null,
        admin_email:    adminEmail,
        admin_password: adminPassword,
        admin_name:     adminName || null,
    };

    // Mostrar spinner
    document.getElementById('loadingArea').classList.remove('d-none');
    document.querySelector('button[onclick="createTenant()"]').disabled = true;

    try {
        const res  = await fetch('/api/empresas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        document.getElementById('loadingArea').classList.add('d-none');
        document.querySelector('button[onclick="createTenant()"]').disabled = false;

        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: '¡Tenant creado!',
                html: `<p>La empresa <strong>${data.data?.nombre}</strong> fue creada correctamente.</p>`,
                confirmButtonText: 'Ver listado',
            }).then(() => {
                window.location.href = '{{ route("admin.tenants.index") }}';
            });
        } else {
            const msgs = data.errors
                ? Object.values(data.errors).flat().join('<br>')
                : (data.message ?? 'Error al crear el tenant');
            showError(msgs);
        }
    } catch(e) {
        document.getElementById('loadingArea').classList.add('d-none');
        document.querySelector('button[onclick="createTenant()"]').disabled = false;
        showError('Error de conexión: ' + e.message);
    }
}

function showError(msg) {
    const el = document.getElementById('formError');
    el.innerHTML = msg;
    el.classList.remove('d-none');
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
@endpush
