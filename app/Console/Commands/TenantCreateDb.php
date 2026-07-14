<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCreateDb extends Command
{
    protected $signature = 'tenant:create-db
                            {empresa_id}
                            {--db-name= : Nombre de la base de datos (por defecto: biometricip_tenant_{id})}
                            {--db-user= : Usuario MySQL (por defecto: el del config)}
                            {--db-pass= : Contraseña MySQL (por defecto: la del config)}';

    protected $description = 'Crea la base de datos para un tenant y la registra en la tabla tenants';

    public function handle(): int
    {
        $empresaId = (int) $this->argument('empresa_id');

        $empresa = DB::table('tbl_empresas')->where('id', $empresaId)->first();
        if (!$empresa) {
            $this->error("Empresa {$empresaId} no encontrada.");
            return Command::FAILURE;
        }

        $dbName  = $this->option('db-name') ?: 'biometricip_tenant_' . $empresaId;
        $dbUser  = $this->option('db-user') ?: config('database.connections.mysql.username');
        $dbPass  = $this->option('db-pass') ?: config('database.connections.mysql.password');

        $this->info("Creando BD `{$dbName}`...");
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $exists = DB::table('tenants')->where('empresa_id', $empresaId)->exists();
        if ($exists) {
            DB::table('tenants')->where('empresa_id', $empresaId)->update([
                'db_name'    => $dbName,
                'db_user'    => $dbUser,
                'db_pass'    => $dbPass,
                'updated_at' => now(),
            ]);
            $this->line('Registro en tenants actualizado.');
        } else {
            DB::table('tenants')->insert([
                'empresa_id' => $empresaId,
                'db_name'    => $dbName,
                'db_user'    => $dbUser,
                'db_pass'    => $dbPass,
                'data'       => '{}',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->line('Registro en tenants creado.');
        }

        $this->info("✓ BD `{$dbName}` lista.");
        return Command::SUCCESS;
    }
}
