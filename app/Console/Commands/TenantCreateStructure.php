<?php

namespace App\Console\Commands;

use App\Helpers\TenantHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCreateStructure extends Command
{
    protected $signature = 'tenant:create-structure {empresa_id}';

    protected $description = 'Crea las tablas de operación en la BD del tenant usando SQL directo';

    public function handle(): int
    {
        $empresaId = (int) $this->argument('empresa_id');

        TenantHelper::switchTenant($empresaId);

        $this->info('Creando tbl_sedes...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_sedes` (
                `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `codigo`     VARCHAR(255) NOT NULL UNIQUE,
                `nombre`     VARCHAR(255) NOT NULL,
                `direccion`  VARCHAR(255) NULL,
                `lat`        DECIMAL(10,7) NOT NULL,
                `lng`        DECIMAL(10,7) NOT NULL,
                `radio_mts`  INT NOT NULL DEFAULT 150,
                `secret_key` VARCHAR(255) NOT NULL,
                `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tbl_registros_asistencia...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_registros_asistencia` (
                `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id`               BIGINT UNSIGNED NOT NULL,
                `sede_id`               BIGINT UNSIGNED NOT NULL,
                `horario_id`            BIGINT UNSIGNED NULL,
                `tipo`                  ENUM('entrada','salida') NOT NULL,
                `lat`                   DECIMAL(10,7) NULL,
                `lng`                   DECIMAL(10,7) NULL,
                `foto_evidencia`        VARCHAR(255) NULL,
                `metodo`                ENUM('qr','biometrico','reconocimiento_facial','foto') NOT NULL,
                `qr_validado`           TINYINT(1) NOT NULL DEFAULT 0,
                `geocerca_validada`     TINYINT(1) NOT NULL DEFAULT 0,
                `distancia_oficina_mts` DECIMAL(8,2) NULL,
                `notas`                 TEXT NULL,
                `fecha_hora`            TIMESTAMP NOT NULL,
                `created_at`            TIMESTAMP NULL,
                `updated_at`            TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_user_id` (`user_id`),
                CONSTRAINT `fk_registros_sede` FOREIGN KEY (`sede_id`) REFERENCES `tbl_sedes` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tbl_fotos_asistencia...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_fotos_asistencia` (
                `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `attendance_record_id` BIGINT UNSIGNED NOT NULL,
                `foto_base64`          LONGTEXT NOT NULL,
                `thumbnail_base64`     TEXT NOT NULL,
                `created_at`           TIMESTAMP NULL,
                `updated_at`           TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_fotos_registro` FOREIGN KEY (`attendance_record_id`) REFERENCES `tbl_registros_asistencia` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tbl_departamentos...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_departamentos` (
                `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre`      VARCHAR(100) NOT NULL UNIQUE,
                `descripcion` VARCHAR(255) NULL,
                `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
                `created_at`  TIMESTAMP NULL,
                `updated_at`  TIMESTAMP NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tbl_cargos...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_cargos` (
                `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre`      VARCHAR(100) NOT NULL,
                `descripcion` VARCHAR(255) NULL,
                `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
                `created_at`  TIMESTAMP NULL,
                `updated_at`  TIMESTAMP NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tbl_horarios...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_horarios` (
                `id`                     BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
                `nombre`                VARCHAR(100)      NOT NULL,
                `hora_entrada`          TIME              NOT NULL,
                `hora_salida`           TIME              NOT NULL,
                `duracion_almuerzo_min` SMALLINT UNSIGNED NULL     DEFAULT NULL,
                `is_active`             TINYINT(1)        NOT NULL DEFAULT 1,
                `created_at`             TIMESTAMP         NULL,
                `updated_at`             TIMESTAMP         NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tbl_permisos...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_permisos` (
                `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `user_id`       BIGINT UNSIGNED  NOT NULL,
                `fecha`         DATE             NOT NULL,
                `tipo`          ENUM('salida_temprana','llegada_tarde','dia_completo','horas') NOT NULL,
                `horas_permiso` DECIMAL(4,2)     NOT NULL DEFAULT 0.00,
                `motivo`        TEXT             NULL,
                `estado`        ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
                `aprobado_por`  BIGINT UNSIGNED  NULL DEFAULT NULL,
                `created_at`    TIMESTAMP        NULL,
                `updated_at`    TIMESTAMP        NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_permisos_user_fecha` (`user_id`, `fecha`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->info('Creando tbl_festivos...');
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `tbl_festivos` (
                `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `fecha`      DATE            NOT NULL,
                `nombre`     VARCHAR(255)    NOT NULL,
                `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP       NULL,
                `updated_at` TIMESTAMP       NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_festivos_fecha` (`fecha`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        TenantHelper::switchToCentral();

        $this->info("✓ Estructura creada para empresa {$empresaId}.");
        return Command::SUCCESS;
    }
}
