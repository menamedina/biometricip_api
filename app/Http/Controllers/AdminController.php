<?php

namespace App\Http\Controllers;

use App\Exports\CargosTemplateExport;
use App\Exports\DepartamentosTemplateExport;
use App\Helpers\TenantHelper;
use App\Imports\CargosImport;
use App\Imports\DepartamentosImport;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\TenantTabla;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

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

    public function departamentosIndex(Request $request): View
    {
        $searchDepto = $request->input('search_depto', '');
        $searchCargo = $request->input('search_cargo', '');

        $deptos = Departamento::orderBy('nombre')
            ->when($searchDepto, fn($q) => $q->where(function ($q) use ($searchDepto) {
                $q->where('nombre', 'like', "%{$searchDepto}%")
                  ->orWhere('descripcion', 'like', "%{$searchDepto}%");
            }))
            ->get();

        $cargos = Cargo::orderBy('nombre')
            ->when($searchCargo, fn($q) => $q->where(function ($q) use ($searchCargo) {
                $q->where('nombre', 'like', "%{$searchCargo}%")
                  ->orWhere('descripcion', 'like', "%{$searchCargo}%");
            }))
            ->get();

        $allDeptoNames = Departamento::orderBy('nombre')->pluck('nombre');
        $allCargoNames = Cargo::orderBy('nombre')->pluck('nombre');

        return view('admin.departamentos.index', compact(
            'deptos', 'cargos', 'searchDepto', 'searchCargo', 'allDeptoNames', 'allCargoNames'
        ));
    }

    public function departamentosStore(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tenant.tbl_departamentos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Departamento::create($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Departamento creado.');
    }

    public function departamentosUpdate(Request $request, int $id)
    {
        $depto = Departamento::findOrFail($id);
        $data  = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tenant.tbl_departamentos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $depto->update($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Departamento actualizado.');
    }

    public function departamentosDestroy(Request $request, int $id)
    {
        $depto = Departamento::findOrFail($id);
        $back  = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        if (User::where('departamento_id', $id)->exists()) {
            $depto->update(['is_active' => false]);
            return redirect()->route('admin.departamentos.index', $back)
                ->with('success', 'El departamento tiene empleados asignados y fue deshabilitado en lugar de eliminado.');
        }

        $depto->delete();
        return redirect()->route('admin.departamentos.index', $back)
            ->with('success', 'Departamento eliminado.');
    }

    public function departamentosTemplate()
    {
        return Excel::download(new DepartamentosTemplateExport(), 'plantilla_departamentos.xlsx');
    }

    public function departamentosImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        $back = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        $import = new DepartamentosImport();
        Excel::import($import, $request->file('file'));

        $msg = "Importación completada: {$import->imported} registros importados.";
        if ($import->skipped) {
            $msg .= ' Omitidos por nombre duplicado: ' . implode(', ', $import->skipped) . '.';
        }

        return redirect()->route('admin.departamentos.index', $back)->with('success', $msg);
    }

    public function cargosStore(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Cargo::create($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Cargo creado.');
    }

    public function cargosUpdate(Request $request, int $id)
    {
        $cargo = Cargo::findOrFail($id);
        $data  = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $cargo->update($data);
        return redirect()->route('admin.departamentos.index', ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')])
            ->with('success', 'Cargo actualizado.');
    }

    public function cargosDestroy(Request $request, int $id)
    {
        $cargo = Cargo::findOrFail($id);
        $back  = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        if (User::where('cargo_id', $id)->exists()) {
            $cargo->update(['is_active' => false]);
            return redirect()->route('admin.departamentos.index', $back)
                ->with('success', 'El cargo tiene empleados asignados y fue deshabilitado en lugar de eliminado.');
        }

        $cargo->delete();
        return redirect()->route('admin.departamentos.index', $back)
            ->with('success', 'Cargo eliminado.');
    }

    public function cargosTemplate()
    {
        return Excel::download(new CargosTemplateExport(), 'plantilla_cargos.xlsx');
    }

    public function cargosImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
        $back = ['search_depto' => $request->input('search_depto'), 'search_cargo' => $request->input('search_cargo')];

        $import = new CargosImport();
        Excel::import($import, $request->file('file'));

        $msg = "Importación completada: {$import->imported} registros importados.";
        if ($import->skipped) {
            $msg .= ' Omitidos por nombre duplicado: ' . implode(', ', $import->skipped) . '.';
        }

        return redirect()->route('admin.departamentos.index', $back)->with('success', $msg);
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

    public function dispositivosIndex(): View
    {
        return view('admin.dispositivos.index');
    }

    public function tenantsIndex(): View
    {
        return view('admin.tenants.index');
    }

    public function tenantsCreate(): View
    {
        return view('admin.tenants.create');
    }

    public function tenantsTablas(): View
    {
        return view('admin.tenants.tablas');
    }

    public function tenantsDescargarSql(): Response
    {
        abort_unless(auth()->user()->admin_tenant ?? false, 403);

        $tablasEstructura = TenantTabla::getTablasEstructura();
        $tablasDatos      = TenantTabla::getTablasDatos();

        // Conectar a la primera BD tenant disponible para leer las estructuras
        // (las tablas tenant NO existen en la BD central)
        $primerTenant = DB::table('tenants')
            ->whereNotNull('db_name')
            ->orderBy('id')
            ->first();

        $usaTenantConn = false;
        if ($primerTenant) {
            try {
                TenantHelper::switchTenant((int) $primerTenant->empresa_id);
                $usaTenantConn = true;
            } catch (\Exception $e) {
                // Si no puede conectar continúa, cada tabla mostrará error
            }
        }

        $conn = $usaTenantConn ? DB::connection('tenant') : DB::connection('mysql');

        $sql   = [];
        $sql[] = "-- ================================================";
        $sql[] = "-- ESQUEMA PARA NUEVA BD TENANT — BiometricIP";
        $sql[] = "-- Generado: " . now()->format('Y-m-d H:i:s');
        if ($usaTenantConn) {
            $sql[] = "-- Estructura obtenida de: " . $primerTenant->db_name;
        }
        $sql[] = "-- Tablas de estructura: " . count($tablasEstructura);
        $sql[] = "-- ================================================";
        $sql[] = "";
        $sql[] = "SET FOREIGN_KEY_CHECKS=0;";
        $sql[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
        $sql[] = "SET time_zone = \"+00:00\";";
        $sql[] = "";
        $sql[] = "-- ================================================";
        $sql[] = "-- ESTRUCTURA DE TABLAS";
        $sql[] = "-- ================================================";
        $sql[] = "";

        foreach ($tablasEstructura as $tableName) {
            try {
                $rows = $conn->select("SHOW CREATE TABLE `{$tableName}`");
                if (empty($rows)) continue;

                $create = $rows[0]->{'Create Table'};
                $create = $this->removeForeignKeys($create);

                $sql[] = "-- Tabla: {$tableName}";
                $sql[] = "DROP TABLE IF EXISTS `{$tableName}`;";
                $sql[] = $create . ";";
                $sql[] = "";
            } catch (\Illuminate\Database\QueryException $e) {
                // 1146 = tabla no existe en esta BD — se omite silenciosamente
                if ($e->getCode() === '42S02') continue;
                $sql[] = "-- ERROR en {$tableName}: " . $e->getMessage();
                $sql[] = "";
            }
        }

        // Datos — siempre de BD central (solo aplica para tablas centrales con copiar_datos=true)
        if (!empty($tablasDatos)) {
            $sql[] = "-- ================================================";
            $sql[] = "-- DATOS (de BD central)";
            $sql[] = "-- ================================================";
            $sql[] = "";

            foreach ($tablasDatos as $tableName) {
                try {
                    $rows = DB::connection('mysql')->table($tableName)->get();
                    if ($rows->isEmpty()) continue;

                    $sql[] = "-- Datos: {$tableName}";
                    foreach ($rows as $row) {
                        $values = array_map(function ($v) {
                            if (is_null($v)) return 'NULL';
                            if (is_numeric($v)) return $v;
                            return "'" . addslashes((string) $v) . "'";
                        }, (array) $row);
                        $sql[] = "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");";
                    }
                    $sql[] = "";
                } catch (\Exception $e) {
                    $sql[] = "-- ERROR datos {$tableName}: " . $e->getMessage();
                    $sql[] = "";
                }
            }
        }

        $sql[] = "SET FOREIGN_KEY_CHECKS=1;";
        $sql[] = "";
        $sql[] = "-- FIN DEL SCRIPT";

        if ($usaTenantConn) {
            TenantHelper::switchToCentral();
        }

        $filename = 'biometricip_tenant_' . now()->format('Y-m-d_His') . '.sql';

        return response(implode("\n", $sql))
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Elimina las líneas CONSTRAINT/FOREIGN KEY de un CREATE TABLE
     * sin usar regex que pueda romper ENUMs u otros tipos con paréntesis.
     */
    private function removeForeignKeys(string $createSql): string
    {
        $lines  = explode("\n", $createSql);
        $clean  = [];
        $prevWasComma = false;

        foreach ($lines as $line) {
            $trimmed = ltrim($line);

            // Saltar líneas de CONSTRAINT ... FOREIGN KEY y FOREIGN KEY directas
            if (preg_match('/^\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN\s+KEY/i', $line) ||
                preg_match('/^\s*FOREIGN\s+KEY/i', $line)) {
                // Eliminar la coma de la línea anterior si la tiene al final
                if (!empty($clean)) {
                    $last = rtrim(end($clean));
                    if (substr($last, -1) === ',') {
                        $clean[count($clean) - 1] = substr($last, 0, -1);
                    }
                }
                continue;
            }

            $clean[] = $line;
        }

        return implode("\n", $clean);
    }
}
