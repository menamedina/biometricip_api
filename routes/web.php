<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PublicAttendanceController;
use App\Http\Controllers\Api\EmpleadoController as ApiEmpleadoController;
use App\Http\Controllers\Api\SedeController as ApiSedeController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AdmsController;
use App\Http\Controllers\Api\AttendanceController as ApiAttendanceController;
use App\Http\Controllers\Api\PermisoController as ApiPermisoController;
use App\Http\Controllers\Api\DeviceController as ApiDeviceController;
use Illuminate\Support\Facades\Route;

// ZKTeco ADMS PUSH — sin autenticación ni CSRF
Route::get ('/iclock/cdata',      [AdmsController::class, 'cdata']);
Route::post('/iclock/cdata',      [AdmsController::class, 'cdata']);
Route::get ('/iclock/getrequest', [AdmsController::class, 'getrequest']);
Route::post('/iclock/devicecmd',  [AdmsController::class, 'devicecmd']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/eliminacion-datos', function () {
    return view('data-deletion');
})->name('data-deletion');

Route::post('/eliminacion-datos', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'nombre'   => 'required|string|max:255',
        'documento'=> 'required|string|max:50',
        'email'    => 'required|email|max:255',
        'pais'     => 'required|in:CO,US,otro',
        'tipo'     => 'required|in:eliminacion_total,eliminacion_parcial,eliminacion_gps,revocacion',
        'motivo'   => 'nullable|string|max:1000',
        'confirma' => 'required|accepted',
    ], [
        'nombre.required'    => 'El nombre es obligatorio.',
        'documento.required' => 'El número de documento es obligatorio.',
        'email.required'     => 'El correo electrónico es obligatorio.',
        'email.email'        => 'Ingrese un correo electrónico válido.',
        'pais.required'      => 'Seleccione su país de residencia.',
        'tipo.required'      => 'Seleccione el tipo de solicitud.',
        'confirma.required'  => 'Debe confirmar que entiende las consecuencias.',
        'confirma.accepted'  => 'Debe marcar la casilla de confirmación.',
    ]);

    $ticket = 'DEL-' . date('Y') . '-' . strtoupper(substr(md5($request->email . now()), 0, 6));

    $tipos = [
        'eliminacion_total'    => 'Eliminar cuenta y todos los datos',
        'eliminacion_parcial'  => 'Eliminar solo datos biométricos (fotos)',
        'eliminacion_gps'      => 'Eliminar solo datos de geolocalización',
        'revocacion'           => 'Revocar autorización de tratamiento',
    ];
    $paises = ['CO' => 'Colombia', 'US' => 'Estados Unidos', 'otro' => 'Otro'];

    $cuerpo = "SOLICITUD DE ELIMINACIÓN DE DATOS PERSONALES\n";
    $cuerpo .= str_repeat('=', 50) . "\n\n";
    $cuerpo .= "Ticket:     {$ticket}\n";
    $cuerpo .= "Fecha:      " . now()->format('d/m/Y H:i:s') . "\n\n";
    $cuerpo .= "Nombre:     {$request->nombre}\n";
    $cuerpo .= "Documento:  {$request->documento}\n";
    $cuerpo .= "Correo:     {$request->email}\n";
    $cuerpo .= "País:       " . ($paises[$request->pais] ?? $request->pais) . "\n";
    $cuerpo .= "Tipo:       " . ($tipos[$request->tipo] ?? $request->tipo) . "\n";
    $cuerpo .= "Motivo:     " . ($request->motivo ?: 'No especificado') . "\n";
    $cuerpo .= "IP origen:  " . $request->ip() . "\n\n";
    $cuerpo .= "Esta solicitud debe procesarse en el plazo legal establecido.\n";
    $cuerpo .= "CO: 15 días hábiles | EE. UU.: 45 días\n";

    \Illuminate\Support\Facades\Mail::raw($cuerpo, function ($msg) use ($ticket, $request) {
        $msg->to(config('mail.from.address'))
            ->subject("[{$ticket}] Solicitud de Eliminación de Datos – {$request->nombre}");
    });

    return redirect()->route('data-deletion')->with([
        'success' => true,
        'ticket'  => $ticket,
    ]);
})->name('data-deletion.submit');

// QR v3 — Formulario público de asistencia (sin autenticación)
Route::get ('/asistencia/{webToken}/{sedeCode}/{token}',                    [PublicAttendanceController::class, 'show'])->name('public.attendance.show');
Route::post('/asistencia/{webToken}/{sedeCode}/{token}',                    [PublicAttendanceController::class, 'store'])->name('public.attendance.store');
Route::post('/asistencia/{webToken}/{sedeCode}/{token}/buscar-visitante',   [PublicAttendanceController::class, 'buscarVisitante'])->name('public.attendance.buscar-visitante');

Route::get('/admin/login',  [LoginController::class, 'showLogin'])->name('admin.login.show');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login');

Route::get('/admin/forgot-password',  [LoginController::class, 'showForgotPassword'])->name('admin.password.request');
Route::post('/admin/forgot-password', [LoginController::class, 'sendResetLink'])->name('admin.password.email');
Route::get('/admin/reset-password/{token}', [LoginController::class, 'showResetPassword'])->name('admin.password.reset');
Route::post('/admin/reset-password',  [LoginController::class, 'resetPassword'])->name('admin.password.update');

// Accesible por todos los roles autenticados
Route::middleware(['auth', 'admin', 'tenancy.session'])->group(function () {
    Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('/admin',            [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/attendance', [AdminController::class, 'attendanceIndex'])->name('admin.attendance.index');
    Route::get('/admin/resumen',         [AdminController::class, 'resumenIndex'])->name('admin.resumen.index');
    Route::get('/admin/resumen/records', [AdminController::class, 'resumenRecords'])->name('admin.resumen.records');
    Route::get ('/admin/attendance/records',      [ApiAttendanceController::class, 'index']);
    Route::get ('/admin/attendance/stats',        [ApiAttendanceController::class, 'stats']);
    Route::post('/admin/attendance/manual',       [ApiAttendanceController::class, 'storeManual']);
    Route::put ('/admin/attendance/{id}',         [ApiAttendanceController::class, 'update']);
    Route::get ('/admin/attendance/{id}/photo',   [ApiAttendanceController::class, 'getPhoto']);
    Route::get ('/admin/catalogos',               [\App\Http\Controllers\Api\DepartamentoController::class, 'catalogos']);
});

// Solo admin y supervisor
Route::middleware(['auth', 'admin', 'role:admin,supervisor', 'tenancy.session'])->group(function () {
    Route::get   ('/admin/sedes',                            [AdminController::class,  'sedesIndex'])->name('admin.sedes.index');
    Route::get   ('/admin/sedes/list',                       [ApiSedeController::class, 'index']);
    Route::post  ('/admin/sedes',                            [ApiSedeController::class, 'store']);
    Route::put   ('/admin/sedes/{sede}',                     [ApiSedeController::class, 'update']);
    Route::delete('/admin/sedes/{sede}',                     [ApiSedeController::class, 'destroy']);
    Route::get   ('/admin/sedes/{sede}/qr',                  [ApiSedeController::class, 'qr']);
    Route::get   ('/admin/sedes/{sede}/qr-static',           [ApiSedeController::class, 'qrStatic']);
    Route::post  ('/admin/sedes/{sede}/qr-static/enable',    [ApiSedeController::class, 'enableStaticQR']);
    Route::post  ('/admin/sedes/{sede}/qr-static/regenerar', [ApiSedeController::class, 'regenerateStaticQR']);
    Route::get   ('/admin/sedes/{sede}/qr-v3',               [ApiSedeController::class, 'qrV3']);
    Route::post  ('/admin/sedes/{sede}/qr-v3/enable',        [ApiSedeController::class, 'enableQRV3']);
    Route::post  ('/admin/sedes/{sede}/qr-v3/regenerar',     [ApiSedeController::class, 'regenerateQRV3']);
    Route::get   ('/admin/empleados',                  [AdminController::class,       'empleadosIndex'])->name('admin.empleados.index');
    Route::get   ('/admin/empleados/list',                             [ApiEmpleadoController::class, 'index']);
    Route::get   ('/admin/empleados/{id}/imagenes-rostro',            [ApiEmpleadoController::class, 'getImagenesRostro']);
    Route::post  ('/admin/empleados/{id}/imagenes-rostro',            [ApiEmpleadoController::class, 'storeImagenRostro']);
    Route::delete('/admin/empleados/{id}/imagenes-rostro/{imageId}',  [ApiEmpleadoController::class, 'destroyImagenRostro']);
    Route::get   ('/admin/empleados/tenant-catalogs',  [AdminController::class,       'empleadosCatalogos']);
    Route::get   ('/admin/empleados/tenant-sedes',     [AdminController::class,       'empleadosSedes']);
    Route::get   ('/admin/empleados/lideres',          [AdminController::class,       'empleadosLideres']);
    Route::get   ('/admin/empleados/template',        [AdminController::class,       'empleadosTemplate'])->name('admin.empleados.template');
    Route::post  ('/admin/empleados/import',          [AdminController::class,       'empleadosImport'])->name('admin.empleados.import');
    Route::get   ('/admin/empleados/{id}/detail',      [ApiEmpleadoController::class, 'show'])->where('id', '[0-9]+');
    Route::post  ('/admin/empleados',                  [ApiEmpleadoController::class, 'store']);
    Route::put   ('/admin/empleados/{token}',             [ApiEmpleadoController::class, 'update']);
    Route::post  ('/admin/empleados/{token}/update',      [ApiEmpleadoController::class, 'update']);
    Route::delete('/admin/empleados/{token}',             [ApiEmpleadoController::class, 'destroy']);
    Route::get ('/admin/visitantes',                          [AdminController::class,      'visitantesIndex'])->name('admin.visitantes.index');
    Route::post('/admin/visitantes',                          [AdminController::class,      'visitantesStore'])->name('admin.visitantes.store');
    Route::get ('/admin/visitantes/list',                     [AdminController::class,      'visitantesList'])->name('admin.visitantes.list');
    Route::post('/admin/visitantes/{id}/forzar-salida',       [AdminController::class,      'visitantesForzarSalida'])->name('admin.visitantes.forzar-salida');
    Route::post('/admin/visitantes/{id}/induccion',           [AdminController::class,      'visitantesInduccion'])->name('admin.visitantes.induccion');
    Route::get ('/admin/visitantes/buscar-cedula',             [AdminController::class,      'visitantesBuscarCedula'])->name('admin.visitantes.buscar-cedula');
    Route::post('/admin/visitantes/{id}/observacion',         [AdminController::class,      'visitantesObservacion'])->name('admin.visitantes.observacion');
    Route::get ('/admin/visitantes/{id}/foto',                [AdminController::class,      'visitantesFoto'])->name('admin.visitantes.foto');
    Route::get('/admin/dispositivos',   [AdminController::class, 'dispositivosIndex'])->name('admin.dispositivos.index');
    Route::get('/admin/permisos',       [AdminController::class, 'permisosIndex'])->name('admin.permisos.index');
    Route::get   ('/admin/departamentos',          [AdminController::class, 'departamentosIndex'])->name('admin.departamentos.index');
    Route::post  ('/admin/departamentos',          [AdminController::class, 'departamentosStore'])->name('admin.departamentos.store');
    Route::put   ('/admin/departamentos/{id}',     [AdminController::class, 'departamentosUpdate'])->name('admin.departamentos.update');
    Route::delete('/admin/departamentos/{id}',     [AdminController::class, 'departamentosDestroy'])->name('admin.departamentos.destroy');
    Route::post  ('/admin/departamentos/import',    [AdminController::class, 'departamentosImport'])->name('admin.departamentos.import');
    Route::get   ('/admin/departamentos/template', [AdminController::class, 'departamentosTemplate'])->name('admin.departamentos.template');
    Route::post  ('/admin/cargos',                 [AdminController::class, 'cargosStore'])->name('admin.cargos.store');
    Route::put   ('/admin/cargos/{id}',            [AdminController::class, 'cargosUpdate'])->name('admin.cargos.update');
    Route::delete('/admin/cargos/{id}',            [AdminController::class, 'cargosDestroy'])->name('admin.cargos.destroy');
    Route::post  ('/admin/cargos/import',           [AdminController::class, 'cargosImport'])->name('admin.cargos.import');
    Route::get   ('/admin/cargos/template',        [AdminController::class, 'cargosTemplate'])->name('admin.cargos.template');
    Route::get   ('/admin/horarios',          [AdminController::class, 'horariosIndex'])->name('admin.horarios.index');
    Route::get   ('/admin/horarios/list',     [\App\Http\Controllers\Api\HorarioController::class, 'index']);
    Route::post  ('/admin/horarios',          [\App\Http\Controllers\Api\HorarioController::class, 'store']);
    Route::put   ('/admin/horarios/{id}',     [\App\Http\Controllers\Api\HorarioController::class, 'update']);
    Route::delete('/admin/horarios/{id}',     [\App\Http\Controllers\Api\HorarioController::class, 'destroy']);
    Route::get   ('/admin/festivos',       [AdminController::class, 'festivosIndex'])->name('admin.festivos.index');
    Route::get   ('/admin/festivos/list',  [\App\Http\Controllers\Api\FestivoController::class, 'index']);
    Route::post  ('/admin/festivos',       [\App\Http\Controllers\Api\FestivoController::class, 'store']);
    Route::put   ('/admin/festivos/{id}',  [\App\Http\Controllers\Api\FestivoController::class, 'update']);
    Route::delete('/admin/festivos/{id}',  [\App\Http\Controllers\Api\FestivoController::class, 'destroy']);
    Route::get   ('/admin/empresas',                    [AdminController::class, 'empresasIndex'])->name('admin.empresas.index');
    Route::get   ('/admin/empresas/list',               [\App\Http\Controllers\Api\EmpresaController::class, 'index']);
    Route::get   ('/admin/empresas/{id}',               [\App\Http\Controllers\Api\EmpresaController::class, 'showById']);
    Route::post  ('/admin/empresas',                    [\App\Http\Controllers\Api\EmpresaController::class, 'store']);
    Route::put   ('/admin/empresas/{id}',               [\App\Http\Controllers\Api\EmpresaController::class, 'updateById']);
    Route::delete('/admin/empresas/{id}',               [\App\Http\Controllers\Api\EmpresaController::class, 'destroyById']);
    Route::post  ('/admin/empresas/{id}/agent-token',   [\App\Http\Controllers\Api\EmpresaController::class, 'generateAgentToken']);
    Route::delete('/admin/empresas/{id}/agent-token',   [\App\Http\Controllers\Api\EmpresaController::class, 'revokeAgentToken']);
    Route::get('/admin/reports/export', [ReportController::class, 'attendance'])->name('admin.reports.export');
    // Permisos
    Route::get   ('/admin/permisos/list',            [ApiPermisoController::class, 'index']);
    Route::post  ('/admin/permisos',                 [ApiPermisoController::class, 'store']);
    Route::post  ('/admin/permisos/{id}/aprobar',    [ApiPermisoController::class, 'aprobar']);
    Route::post  ('/admin/permisos/{id}/rechazar',   [ApiPermisoController::class, 'rechazar']);
    Route::delete('/admin/permisos/{id}',            [ApiPermisoController::class, 'destroy']);
    // Dispositivos biométricos
    Route::post  ('/admin/dispositivos/ping',              [ApiDeviceController::class, 'ping']);
    Route::get   ('/admin/dispositivos/list',              [ApiDeviceController::class, 'index']);
    Route::post  ('/admin/dispositivos',                   [ApiDeviceController::class, 'store']);
    Route::put   ('/admin/dispositivos/{id}',              [ApiDeviceController::class, 'update']);
    Route::delete('/admin/dispositivos/{id}',              [ApiDeviceController::class, 'destroy']);
    Route::get   ('/admin/dispositivos/{id}/test',         [ApiDeviceController::class, 'testConnection']);
    Route::get   ('/admin/dispositivos/{id}/users',        [ApiDeviceController::class, 'deviceUsers']);
    Route::post  ('/admin/dispositivos/{id}/sync',         [ApiDeviceController::class, 'syncAttendance']);
    Route::post  ('/admin/dispositivos/{id}/clear',        [ApiDeviceController::class, 'clearDevice']);
    Route::get   ('/admin/dispositivos/{id}/sync-history', [ApiDeviceController::class, 'syncHistory']);
});

Route::middleware(['auth', 'admin', 'admin.tenant', 'tenancy.session'])->group(function () {
    Route::get('/admin/tenants',              [AdminController::class, 'tenantsIndex'])->name('admin.tenants.index');
    Route::get('/admin/tenants/create',       [AdminController::class, 'tenantsCreate'])->name('admin.tenants.create');
    Route::get('/admin/tenants/tablas',       [AdminController::class, 'tenantsTablas'])->name('admin.tenants.tablas');
    Route::get('/admin/tenants/descargar-sql',[AdminController::class, 'tenantsDescargarSql'])->name('admin.tenants.descargar-sql');
});
