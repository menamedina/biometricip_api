<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SedeController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\KioscoController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\DepartamentoController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\PermisoController;
use App\Http\Controllers\Api\FestivoController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\AdmsController;
use App\Http\Controllers\Api\VisitanteController;
use Illuminate\Support\Facades\Route;

// ADMS — ZKTeco PUSH (sin autenticación, el dispositivo envía marcaciones)
Route::get ('/iclock/cdata',      [AdmsController::class, 'cdata']);
Route::post('/iclock/cdata',      [AdmsController::class, 'cdata']);
Route::get ('/iclock/getrequest', [AdmsController::class, 'getrequest']);
Route::post('/iclock/devicecmd',  [AdmsController::class, 'devicecmd']);

// Pública
Route::post('/auth/login', [AuthController::class, 'login']);

// Autenticadas con tenancy (switch automático al tenant del usuario)
Route::middleware(['auth:sanctum', 'tenancy'])->group(function () {
    Route::post('/auth/logout',          [AuthController::class, 'logout']);
    Route::get('/auth/me',               [AuthController::class, 'me']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    Route::post('/attendance/clock',        [AttendanceController::class, 'clock']);
    Route::post('/attendance/offline-sync', [AttendanceController::class, 'offlineSync']);
    Route::get('/attendance/my-history',    [AttendanceController::class, 'myHistory']);
    Route::get('/attendance/my-report',     [AttendanceController::class, 'myReport']);

    // Kiosco (cualquier usuario autenticado tipo kiosco)
    Route::post('/kiosco/identificar',              [KioscoController::class, 'identificar']);
    Route::get ('/kiosco/empleados-descriptores',   [KioscoController::class, 'empleadosDescriptores']);

    Route::middleware('admin')->group(function () {
        Route::apiResource('sedes', SedeController::class);
        Route::get ('/sedes/{sede}/qr',                   [SedeController::class, 'qr']);
        Route::get ('/sedes/{sede}/qr-static',            [SedeController::class, 'qrStatic']);
        Route::post('/sedes/{sede}/qr-static/enable',     [SedeController::class, 'enableStaticQR']);
        Route::post('/sedes/{sede}/qr-static/regenerar',  [SedeController::class, 'regenerateStaticQR']);
        Route::get ('/sedes/{sede}/qr-v3',                [SedeController::class, 'qrV3']);
        Route::post('/sedes/{sede}/qr-v3/enable',         [SedeController::class, 'enableQRV3']);
        Route::post('/sedes/{sede}/qr-v3/regenerar',      [SedeController::class, 'regenerateQRV3']);

        Route::get('/empleados/departamentos/list', [EmpleadoController::class, 'departamentos']);
        Route::apiResource('empleados', EmpleadoController::class);
        Route::post  ('/empleados/{id}/face-descriptor',           [EmpleadoController::class, 'updateFaceDescriptor']);
        Route::get   ('/empleados/{id}/imagenes-rostro',           [EmpleadoController::class, 'getImagenesRostro']);
        Route::post  ('/empleados/{id}/imagenes-rostro',           [EmpleadoController::class, 'storeImagenRostro']);
        Route::delete('/empleados/{id}/imagenes-rostro/{imageId}', [EmpleadoController::class, 'destroyImagenRostro']);

        Route::get('/attendance',             [AttendanceController::class, 'index']);
        Route::put('/attendance/{id}',        [AttendanceController::class, 'update']);
        Route::post('/attendance/manual',     [AttendanceController::class, 'storeManual']);
        Route::get('/attendance/latest',      [AttendanceController::class, 'latestRecords']);
        Route::get('/attendance/stats',       [AttendanceController::class, 'stats']);
        Route::get('/attendance/{id}/photo',  [AttendanceController::class, 'getPhoto']);

        Route::get('/reports/attendance',      [ReportController::class, 'attendance']);
        Route::get('/reports/employee-stats',  [ReportController::class, 'employeeStats']);

        // Catálogos combinados
        Route::get('/catalogos', [DepartamentoController::class, 'catalogos']);

        // Departamentos y Cargos
        Route::get   ('/departamentos',           [DepartamentoController::class, 'index']);
        Route::post  ('/departamentos',           [DepartamentoController::class, 'store']);
        Route::put   ('/departamentos/{id}',      [DepartamentoController::class, 'update']);
        Route::delete('/departamentos/{id}',      [DepartamentoController::class, 'destroy']);
        Route::get   ('/cargos',                  [DepartamentoController::class, 'cargos']);
        Route::post  ('/cargos',                  [DepartamentoController::class, 'storeCargo']);
        Route::put   ('/cargos/{id}',             [DepartamentoController::class, 'updateCargo']);
        Route::delete('/cargos/{id}',             [DepartamentoController::class, 'destroyCargo']);

        // Horarios
        Route::apiResource('horarios', HorarioController::class);

        // Permisos
        Route::get   ('/permisos',             [PermisoController::class, 'index']);
        Route::post  ('/permisos',             [PermisoController::class, 'store']);
        Route::put   ('/permisos/{id}',        [PermisoController::class, 'update']);
        Route::post  ('/permisos/{id}/aprobar',[PermisoController::class, 'aprobar']);
        Route::post  ('/permisos/{id}/rechazar',[PermisoController::class, 'rechazar']);
        Route::delete('/permisos/{id}',        [PermisoController::class, 'destroy']);

        // Festivos
        Route::apiResource('festivos', FestivoController::class);

        // Empresa propia (admin normal)
        Route::get('/mi-empresa',        [EmpresaController::class, 'miEmpresa']);
        Route::put('/mi-empresa',        [EmpresaController::class, 'updateMiEmpresa']);

        // Visitantes
        Route::get ('/visitantes',                    [VisitanteController::class, 'index']);
        Route::get ('/visitantes/{id}/foto',          [VisitanteController::class, 'foto']);
        Route::post('/visitantes/{id}/forzar-salida', [VisitanteController::class, 'forzarSalida']);

        // CRUD Empresas (solo admin_tenant)
        Route::get('/empresas',           [EmpresaController::class, 'index']);
        Route::post('/empresas',          [EmpresaController::class, 'store']);
        Route::get('/empresas/{id}',      [EmpresaController::class, 'showById']);
        Route::put('/empresas/{id}',      [EmpresaController::class, 'updateById']);
        Route::delete('/empresas/{id}',   [EmpresaController::class, 'destroyById']);

        // Dispositivos biométricos ZKTeco
        Route::post('/devices/ping',                    [DeviceController::class, 'ping']);
        Route::apiResource('devices', DeviceController::class);
        Route::get ('/devices/{id}/test',               [DeviceController::class, 'testConnection']);
        Route::get ('/devices/{id}/users',              [DeviceController::class, 'deviceUsers']);
        Route::post('/devices/{id}/sync',               [DeviceController::class, 'syncAttendance']);
        Route::post('/devices/{id}/clear',              [DeviceController::class, 'clearDevice']);
        Route::get ('/devices/{id}/sync-history',       [DeviceController::class, 'syncHistory']);
    });
});

// Las rutas de empresas ahora están en el grupo admin con verificación admin_tenant
