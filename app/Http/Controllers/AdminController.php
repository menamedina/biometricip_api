<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function sedesIndex(): View
    {
        return view('admin.sedes.index');
    }

    public function empleadosIndex(): View
    {
        return view('admin.empleados.index');
    }

    public function attendanceIndex(): View
    {
        return view('admin.attendance.index');
    }
}
