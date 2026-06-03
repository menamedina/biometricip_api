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

    public function resumenIndex(): View
    {
        return view('admin.resumen.index');
    }

    public function departamentosIndex(): View
    {
        return view('admin.departamentos.index');
    }

    public function horariosIndex(): View
    {
        return view('admin.horarios.index');
    }

    public function permisosIndex(): View
    {
        return view('admin.permisos.index');
    }

    public function festivosIndex(): View
    {
        return view('admin.festivos.index');
    }

    public function empresasIndex(): View
    {
        return view('admin.empresas.index');
    }

    public function visitantesIndex(): View
    {
        return view('admin.visitantes.index');
    }
}
