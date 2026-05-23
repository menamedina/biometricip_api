<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login.show');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/sedes', [AdminController::class, 'sedesIndex'])->name('admin.sedes.index');
    Route::get('/admin/empleados', [AdminController::class, 'empleadosIndex'])->name('admin.empleados.index');
    Route::get('/admin/attendance', [AdminController::class, 'attendanceIndex'])->name('admin.attendance.index');
    Route::get('/admin/reports/export', [ReportController::class, 'attendance'])->name('admin.reports.export');
});
