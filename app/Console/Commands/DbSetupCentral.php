<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DbSetupCentral extends Command
{
    protected $signature = 'db:setup-central
                            {--superadmin-email=superadmin@biometricip.com}
                            {--superadmin-password=superadmin123}
                            {--force : Omitir confirmación}';

    protected $description = 'Crea la estructura central: tbl_empresas, tenants, empresa_id en users y superadmin';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('¿Crear estructura central en la BD biometricip?', true)) {
            return Command::SUCCESS;
        }

        $this->info('Creando tabla tbl_empresas...');
        DB::statement("
            CREATE TABLE IF NOT EXISTS `tbl_empresas` (
                `id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre`    VARCHAR(255) NOT NULL,
                `ruc`       VARCHAR(20) NULL UNIQUE,
                `email`     VARCHAR(255) NULL,
                `telefono`  VARCHAR(20) NULL,
                `logo_url`  VARCHAR(255) NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tabla tenants...');
        DB::statement("
            CREATE TABLE IF NOT EXISTS `tenants` (
                `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `empresa_id` BIGINT UNSIGNED NOT NULL UNIQUE,
                `db_name`    VARCHAR(255) NOT NULL UNIQUE,
                `db_user`    VARCHAR(255) NULL,
                `db_pass`    VARCHAR(255) NULL,
                `data`       JSON NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_tenants_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `tbl_empresas` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Agregando empresa_id a users...');
        $columns = DB::select("SHOW COLUMNS FROM `users` LIKE 'empresa_id'");
        if (empty($columns)) {
            DB::statement("
                ALTER TABLE `users`
                ADD COLUMN `empresa_id` BIGINT UNSIGNED NULL AFTER `is_active`,
                ADD CONSTRAINT `fk_users_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `tbl_empresas` (`id`) ON DELETE SET NULL
            ");
            $this->info('Columna empresa_id agregada.');
        } else {
            $this->line('Columna empresa_id ya existe. Omitida.');
        }

        $this->info('Agregando columnas de empleado a users...');
        $employeeColumns = [
            'codigo_empleado' => "ALTER TABLE `users` ADD COLUMN `codigo_empleado` VARCHAR(255) NULL AFTER `empresa_id`",
            'departamento'    => "ALTER TABLE `users` ADD COLUMN `departamento` VARCHAR(255) NULL AFTER `codigo_empleado`",
            'cargo'           => "ALTER TABLE `users` ADD COLUMN `cargo` VARCHAR(255) NULL AFTER `departamento`",
            'telefono'        => "ALTER TABLE `users` ADD COLUMN `telefono` VARCHAR(20) NULL AFTER `cargo`",
            'foto_url'        => "ALTER TABLE `users` ADD COLUMN `foto_url` VARCHAR(255) NULL AFTER `telefono`",
            'face_descriptor' => "ALTER TABLE `users` ADD COLUMN `face_descriptor` JSON NULL AFTER `foto_url`",
        ];
        foreach ($employeeColumns as $col => $sql) {
            $exists = DB::select("SHOW COLUMNS FROM `users` LIKE '{$col}'");
            if (empty($exists)) {
                DB::statement($sql);
                $this->info("Columna {$col} agregada.");
            } else {
                $this->line("Columna {$col} ya existe. Omitida.");
            }
        }

        // Índice único compuesto empresa_id + codigo_empleado
        $indexExists = DB::select("SHOW INDEX FROM `users` WHERE Key_name = 'uq_empresa_codigo'");
        if (empty($indexExists)) {
            DB::statement("ALTER TABLE `users` ADD UNIQUE KEY `uq_empresa_codigo` (`empresa_id`, `codigo_empleado`)");
            $this->info('Índice único empresa_id+codigo_empleado creado.');
        } else {
            $this->line('Índice uq_empresa_codigo ya existe. Omitido.');
        }

        $email    = $this->option('superadmin-email');
        $password = $this->option('superadmin-password');

        $exists = DB::table('users')->where('email', $email)->exists();
        if (!$exists) {
            DB::table('users')->insert([
                'name'        => 'Super Administrador',
                'email'       => $email,
                'password'    => Hash::make($password),
                'role'        => 'admin',
                'is_active'   => 1,
                'empresa_id'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $this->info("Superadmin creado: {$email} / {$password}");
        } else {
            $this->line("Superadmin {$email} ya existe. Omitido.");
        }

        $this->info('✓ Estructura central completada.');
        return Command::SUCCESS;
    }
}
