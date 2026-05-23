<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SedeController;
use App\Http\Controllers\Api\EmpleadoController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('sedes', SedeController::class);
    Route::get('/sedes/{sede}/qr', [SedeController::class, 'qr']);

    Route::apiResource('empleados', EmpleadoController::class);
    Route::post('/empleados/{empleado}/face-descriptor', [EmpleadoController::class, 'updateFaceDescriptor']);
    Route::get('/empleados/departamentos/list', [EmpleadoController::class, 'departamentos']);

    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::get('/attendance/latest', [AttendanceController::class, 'latestRecords']);
    Route::get('/attendance/stats', [AttendanceController::class, 'stats']);
    Route::get('/attendance/{id}/photo', [AttendanceController::class, 'getPhoto']);

    Route::get('/reports/attendance', [ReportController::class, 'attendance']);
    Route::get('/reports/employee-stats', [ReportController::class, 'employeeStats']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/attendance/clock', [AttendanceController::class, 'clock']);
    Route::get('/attendance/my-history', [AttendanceController::class, 'myHistory']);
});
