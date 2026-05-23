<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create
                            {--nombre= : Nombre de la empresa}
                            {--ruc= : RUC o identificación fiscal}
                            {--email= : Email del administrador de empresa}
                            {--telefono= : Teléfono de la empresa}
                            {--admin-name= : Nombre del administrador}
                            {--admin-password=password123 : Password del admin}';

    protected $description = 'Crea una empresa completa con BD, estructura y usuario administrador';

    public function handle(): int
    {
        $nombre   = $this->option('nombre') ?? $this->ask('Nombre de la empresa');
        $ruc      = $this->option('ruc');
        $email    = $this->option('email') ?? $this->ask('Email del administrador');
        $telefono = $this->option('telefono');
        $password = $this->option('admin-password');
        $adminName = $this->option('admin-name');

        $this->info("Creando empresa: {$nombre}");

        $empresaId = DB::table('tbl_empresas')->insertGetId([
            'nombre'     => $nombre,
            'ruc'        => $ruc,
            'email'      => $email,
            'telefono'   => $telefono,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("Empresa creada con ID: {$empresaId}");

        $this->call('tenant:create-db', ['empresa_id' => $empresaId]);
        $this->call('tenant:create-structure', ['empresa_id' => $empresaId]);
        $this->call('tenant:seed', [
            'empresa_id'       => $empresaId,
            '--admin-email'    => $email,
            '--admin-name'     => $adminName,
            '--admin-password' => $password,
        ]);

        $this->newLine();
        $this->info("✓ Tenant '{$nombre}' creado exitosamente.");
        $this->table(['Campo', 'Valor'], [
            ['Empresa ID', $empresaId],
            ['BD',         'biometricip_tenant_' . $empresaId],
            ['Admin email', $email],
            ['Admin pass',  $password],
        ]);

        return Command::SUCCESS;
    }
}
