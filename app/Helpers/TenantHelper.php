<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantHelper
{
    /**
     * Cambia la conexión 'tenant' a la BD de la empresa indicada.
     * Lee db_name, db_user y db_pass de la tabla tenants en la BD central.
     */
    public static function switchTenant(int $empresaId): void
    {
        $tenant = DB::connection('mysql')
            ->table('tenants')
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$tenant) {
            Log::error('TenantHelper: tenant no encontrado', ['empresa_id' => $empresaId]);
            throw new \Exception("Tenant para empresa {$empresaId} no encontrado.");
        }

        // Log::info('TenantHelper: switching tenant', [
        //     'empresa_id' => $empresaId,
        //     'db_name'    => $tenant->db_name,
        //     'db_user'    => $tenant->db_user,
        // ]);

        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_user ?? config('database.connections.mysql.username'));
        Config::set('database.connections.tenant.password', $tenant->db_pass ?? config('database.connections.mysql.password'));

        DB::purge('tenant');
        DB::reconnect('tenant');

        // Log::info('TenantHelper: conexión tenant establecida', ['db_name' => $tenant->db_name]);
    }

    /**
     * Limpia la conexión tenant. Útil en comandos Artisan que iteran tenants.
     */
    public static function switchToCentral(): void
    {
        DB::purge('tenant');
    }
}
