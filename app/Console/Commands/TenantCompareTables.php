<?php

namespace App\Console\Commands;

use App\Helpers\TenantHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCompareTables extends Command
{
    protected $signature = 'tenant:compare-tables {empresa_id? : ID de empresa (por defecto la primera disponible)}';

    protected $description = 'Compara tablas reales del tenant vs configuración en tbl_admin_tenant';

    public function handle(): int
    {
        // Determinar empresa a usar
        $empresaId = $this->argument('empresa_id');

        if (!$empresaId) {
            $primer = DB::table('tenants')->whereNotNull('db_name')->orderBy('id')->first();
            if (!$primer) {
                $this->error('No hay tenants con base de datos configurada.');
                return Command::FAILURE;
            }
            $empresaId = $primer->empresa_id;
        }

        // Conectar al tenant
        try {
            TenantHelper::switchTenant((int) $empresaId);
        } catch (\Exception $e) {
            $this->error('No se pudo conectar al tenant: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $dbName = DB::connection('tenant')->getDatabaseName();
        $this->info("Comparando contra BD tenant: {$dbName}");
        $this->newLine();

        // Tablas reales en el tenant
        $rows       = DB::connection('tenant')->select('SHOW TABLES');
        $key        = 'Tables_in_' . $dbName;
        $enTenant   = collect($rows)->pluck($key)->sort()->values();

        // Tablas configuradas en tbl_admin_tenant
        $enConfig   = DB::table('tbl_admin_tenant')->pluck('nombre_tabla')->sort()->values();

        // ── Solo en tenant (no están en config) ──────────────────────────────
        $soloTenant = $enTenant->diff($enConfig);

        $this->warn('=== SOLO EN TENANT — faltan en tbl_admin_tenant (' . $soloTenant->count() . ') ===');
        if ($soloTenant->isEmpty()) {
            $this->line('  (ninguna)');
        } else {
            foreach ($soloTenant as $tabla) {
                $this->line("  <fg=yellow>+</> {$tabla}");
            }
        }

        $this->newLine();

        // ── Solo en config (no existen en el tenant) ─────────────────────────
        // Excluir las marcadas como BD central (se espera que no estén en el tenant)
        $centrales    = DB::table('tbl_admin_tenant')->where('es_bd_central', 1)->pluck('nombre_tabla');
        $soloConfig   = $enConfig->diff($enTenant)->diff($centrales);
        $centralAusenteTenant = $enConfig->diff($enTenant)->intersect($centrales);

        $this->warn('=== SOLO EN CONFIG (no existen en tenant, NO son centrales) (' . $soloConfig->count() . ') ===');
        if ($soloConfig->isEmpty()) {
            $this->line('  (ninguna)');
        } else {
            foreach ($soloConfig as $tabla) {
                $this->line("  <fg=red>-</> {$tabla}");
            }
        }

        $this->newLine();

        // ── En ambos ─────────────────────────────────────────────────────────
        $enAmbos = $enTenant->intersect($enConfig);

        $this->info('=== EN AMBOS — correctamente configuradas (' . $enAmbos->count() . ') ===');
        foreach ($enAmbos as $tabla) {
            $cfg = DB::table('tbl_admin_tenant')->where('nombre_tabla', $tabla)->first();
            $flags = [];
            if ($cfg->es_bd_central)     $flags[] = '<fg=red>central</>';
            if ($cfg->copiar_estructura) $flags[] = '<fg=blue>estructura</>';
            if ($cfg->copiar_datos)      $flags[] = '<fg=green>datos</>';
            if (!$cfg->activo)           $flags[] = '<fg=gray>inactiva</>';
            $this->line('  <fg=green>✓</> ' . str_pad($tabla, 40) . implode(', ', $flags));
        }

        $this->newLine();

        // ── Resumen ───────────────────────────────────────────────────────────
        $this->table(
            ['', 'Cantidad'],
            [
                ['Tablas en tenant',                   $enTenant->count()],
                ['Tablas en config (tbl_admin_tenant)', $enConfig->count()],
                ['Faltan en config',                    $soloTenant->count()],
                ['En config pero no en tenant',         $soloConfig->count()],
                ['Coinciden',                           $enAmbos->count()],
            ]
        );

        TenantHelper::switchToCentral();

        return Command::SUCCESS;
    }
}
