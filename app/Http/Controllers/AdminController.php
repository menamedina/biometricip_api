<?php

namespace App\Http\Controllers;

use App\Helpers\TenantHelper;
use App\Models\TenantTabla;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
