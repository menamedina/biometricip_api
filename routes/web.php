<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login',  [LoginController::class, 'showLogin'])->name('admin.login.show');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login');

Route::middleware(['auth', 'admin', 'tenancy.session'])->group(function () {
    Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/sedes', [AdminController::class, 'sedesIndex'])->name('admin.sedes.index');
    Route::get('/admin/empleados', [AdminController::class, 'empleadosIndex'])->name('admin.empleados.index');
    Route::get('/admin/attendance', [AdminController::class, 'attendanceIndex'])->name('admin.attendance.index');
    Route::get('/admin/resumen', [AdminController::class, 'resumenIndex'])->name('admin.resumen.index');
    Route::get('/admin/departamentos', [AdminController::class, 'departamentosIndex'])->name('admin.departamentos.index');
    Route::get('/admin/horarios',      [AdminController::class, 'horariosIndex'])->name('admin.horarios.index');
    Route::get('/admin/permisos',      [AdminController::class, 'permisosIndex'])->name('admin.permisos.index');
    Route::get('/admin/festivos',      [AdminController::class, 'festivosIndex'])->name('admin.festivos.index');
    Route::get('/admin/empresas',      [AdminController::class, 'empresasIndex'])->name('admin.empresas.index');
    Route::get('/admin/reports/export', [ReportController::class, 'attendance'])->name('admin.reports.export');
});
