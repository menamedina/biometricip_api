<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PublicAttendanceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AdmsController;
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
Route::get ('/asistencia/{webToken}/{sedeCode}/{token}', [PublicAttendanceController::class, 'show'])->name('public.attendance.show');
Route::post('/asistencia/{webToken}/{sedeCode}/{token}', [PublicAttendanceController::class, 'store'])->name('public.attendance.store');

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
    Route::get('/admin/resumen',    [AdminController::class, 'resumenIndex'])->name('admin.resumen.index');
});

// Solo admin y supervisor
Route::middleware(['auth', 'admin', 'role:admin,supervisor', 'tenancy.session'])->group(function () {
    Route::get('/admin/sedes',          [AdminController::class, 'sedesIndex'])->name('admin.sedes.index');
    Route::get('/admin/empleados',      [AdminController::class, 'empleadosIndex'])->name('admin.empleados.index');
    Route::get('/admin/visitantes',     [AdminController::class, 'visitantesIndex'])->name('admin.visitantes.index');
    Route::get('/admin/dispositivos',   [AdminController::class, 'dispositivosIndex'])->name('admin.dispositivos.index');
    Route::get('/admin/permisos',       [AdminController::class, 'permisosIndex'])->name('admin.permisos.index');
    Route::get('/admin/departamentos',  [AdminController::class, 'departamentosIndex'])->name('admin.departamentos.index');
    Route::get('/admin/horarios',       [AdminController::class, 'horariosIndex'])->name('admin.horarios.index');
    Route::get('/admin/festivos',       [AdminController::class, 'festivosIndex'])->name('admin.festivos.index');
    Route::get('/admin/empresas',       [AdminController::class, 'empresasIndex'])->name('admin.empresas.index');
    Route::get('/admin/reports/export', [ReportController::class, 'attendance'])->name('admin.reports.export');
});

Route::middleware(['auth', 'admin', 'admin.tenant', 'tenancy.session'])->group(function () {
    Route::get('/admin/tenants',              [AdminController::class, 'tenantsIndex'])->name('admin.tenants.index');
    Route::get('/admin/tenants/create',       [AdminController::class, 'tenantsCreate'])->name('admin.tenants.create');
    Route::get('/admin/tenants/tablas',       [AdminController::class, 'tenantsTablas'])->name('admin.tenants.tablas');
    Route::get('/admin/tenants/descargar-sql',[AdminController::class, 'tenantsDescargarSql'])->name('admin.tenants.descargar-sql');
});
