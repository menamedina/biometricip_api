<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SedeController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\EmpresaController;
use Illuminate\Support\Facades\Route;

// Pública
Route::post('/auth/login', [AuthController::class, 'login']);

// Autenticadas con tenancy (switch automático al tenant del usuario)
Route::middleware(['auth:sanctum', 'tenancy'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    Route::post('/attendance/clock',        [AttendanceController::class, 'clock']);
    Route::get('/attendance/my-history',    [AttendanceController::class, 'myHistory']);

    Route::middleware('admin')->group(function () {
        Route::apiResource('sedes', SedeController::class);
        Route::get('/sedes/{sede}/qr', [SedeController::class, 'qr']);

        Route::get('/empleados/departamentos/list', [EmpleadoController::class, 'departamentos']);
        Route::apiResource('empleados', EmpleadoController::class);
        Route::post('/empleados/{id}/face-descriptor', [EmpleadoController::class, 'updateFaceDescriptor']);

        Route::get('/attendance',             [AttendanceController::class, 'index']);
        Route::get('/attendance/latest',      [AttendanceController::class, 'latestRecords']);
        Route::get('/attendance/stats',       [AttendanceController::class, 'stats']);
        Route::get('/attendance/{id}/photo',  [AttendanceController::class, 'getPhoto']);

        Route::get('/reports/attendance',      [ReportController::class, 'attendance']);
        Route::get('/reports/employee-stats',  [ReportController::class, 'employeeStats']);
    });
});

// Solo superadmin (empresa_id = null)
Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    Route::apiResource('empresas', EmpresaController::class);
});
