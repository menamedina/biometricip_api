<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantSeed extends Command
{
    protected $signature = 'tenant:seed
                            {empresa_id}
                            {--admin-email= : Email del administrador de empresa}
                            {--admin-name= : Nombre del administrador}
                            {--admin-password=password123}';

    protected $description = 'Crea el usuario administrador inicial para un tenant';

    public function handle(): int
    {
        $empresaId = (int) $this->argument('empresa_id');

        $empresa = DB::table('tbl_empresas')->where('id', $empresaId)->first();
        if (!$empresa) {
            $this->error("Empresa {$empresaId} no encontrada.");
            return Command::FAILURE;
        }

        $email    = $this->option('admin-email') ?? $empresa->email;
        $name     = $this->option('admin-name') ?? 'Admin ' . $empresa->nombre;
        $password = $this->option('admin-password');

        if (!$email) {
            $email = $this->ask('Email del administrador de empresa');
        }

        $exists = DB::table('users')->where('email', $email)->exists();
        if ($exists) {
            $this->line("Usuario {$email} ya existe. Omitido.");
            return Command::SUCCESS;
        }

        DB::table('users')->insert([
            'name'            => $name,
            'email'           => $email,
            'password'        => Hash::make($password),
            'role'            => 'admin',
            'is_active'       => 1,
            'empresa_id'      => $empresaId,
            'codigo_empleado' => 'ADM-0001',
            'cargo'           => 'Administrador',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->info("Usuario admin creado: {$email} / {$password}");
        $this->info("✓ Seed completado para empresa {$empresaId}.");
        return Command::SUCCESS;
    }
}
