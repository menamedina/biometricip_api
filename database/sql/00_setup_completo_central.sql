-- ============================================================
-- BiometricIP — Setup COMPLETO de BD Central
-- Crear primero la BD:
--   CREATE DATABASE biometricip CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE biometricip;
-- Luego ejecutar este script.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Tablas de Laravel (framework)
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch`     INT          NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(255)     NOT NULL,
    `email`             VARCHAR(255)     NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP        NULL,
    `password`          VARCHAR(255)     NOT NULL,
    `role`              ENUM('admin','empleado') NOT NULL DEFAULT 'empleado',
    `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
    `empresa_id`        BIGINT UNSIGNED  NULL,
    `codigo_empleado`   VARCHAR(255)     NULL,
    `departamento`      VARCHAR(255)     NULL,
    `cargo`             VARCHAR(255)     NULL,
    `telefono`          VARCHAR(20)      NULL,
    `foto_url`          VARCHAR(255)     NULL,
    `face_descriptor`   JSON             NULL,
    `remember_token`    VARCHAR(100)     NULL,
    `created_at`        TIMESTAMP        NULL,
    `updated_at`        TIMESTAMP        NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_empresa_codigo` (`empresa_id`, `codigo_empleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP    NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
    `id`            VARCHAR(255)    NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL,
    `ip_address`    VARCHAR(45)     NULL,
    `user_agent`    TEXT            NULL,
    `payload`       LONGTEXT        NOT NULL,
    `last_activity` INT             NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `sessions_user_id_index`       (`user_id`),
    INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
    `key`        VARCHAR(255) NOT NULL,
    `value`      MEDIUMTEXT   NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key`        VARCHAR(255) NOT NULL,
    `owner`      VARCHAR(255) NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
    `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255)     NOT NULL,
    `payload`      LONGTEXT         NOT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED     NULL,
    `available_at` INT UNSIGNED     NOT NULL,
    `created_at`   INT UNSIGNED     NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id`             VARCHAR(255) NOT NULL,
    `name`           VARCHAR(255) NOT NULL,
    `total_jobs`     INT          NOT NULL,
    `pending_jobs`   INT          NOT NULL,
    `failed_jobs`    INT          NOT NULL,
    `failed_job_ids` LONGTEXT     NOT NULL,
    `options`        MEDIUMTEXT   NULL,
    `cancelled_at`   INT          NULL,
    `created_at`     INT          NOT NULL,
    `finished_at`    INT          NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`       VARCHAR(255)    NOT NULL UNIQUE,
    `connection` TEXT            NOT NULL,
    `queue`      TEXT            NOT NULL,
    `payload`    LONGTEXT        NOT NULL,
    `exception`  LONGTEXT        NOT NULL,
    `failed_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tokenable_type` VARCHAR(255)    NOT NULL,
    `tokenable_id`   BIGINT UNSIGNED NOT NULL,
    `name`           VARCHAR(255)    NOT NULL,
    `token`          VARCHAR(64)     NOT NULL UNIQUE,
    `abilities`      TEXT            NULL,
    `last_used_at`   TIMESTAMP       NULL,
    `expires_at`     TIMESTAMP       NULL,
    `created_at`     TIMESTAMP       NULL,
    `updated_at`     TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    INDEX `personal_access_tokens_tokenable_index` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: tbl_empresas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_empresas` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(255)    NOT NULL,
    `ruc`        VARCHAR(20)     NULL UNIQUE,
    `email`      VARCHAR(255)    NULL,
    `telefono`   VARCHAR(20)     NULL,
    `logo_url`   VARCHAR(255)    NULL,
    `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP       NULL,
    `updated_at` TIMESTAMP       NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: tenants (credenciales de BD por empresa)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tenants` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `db_name`    VARCHAR(255)    NOT NULL UNIQUE,
    `db_user`    VARCHAR(255)    NULL,
    `db_pass`    VARCHAR(255)    NULL,
    `data`       JSON            NULL,
    `created_at` TIMESTAMP       NULL,
    `updated_at` TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_tenants_empresa`
        FOREIGN KEY (`empresa_id`) REFERENCES `tbl_empresas` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- FK de users → tbl_empresas
-- ------------------------------------------------------------
ALTER TABLE `users`
    ADD CONSTRAINT `fk_users_empresa`
        FOREIGN KEY (`empresa_id`) REFERENCES `tbl_empresas` (`id`)
        ON DELETE SET NULL;

-- ------------------------------------------------------------
-- Superadmin de plataforma (empresa_id = NULL)
-- Contraseña: superadmin123  — cambiar después del primer login
-- ------------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`, `empresa_id`, `created_at`, `updated_at`)
VALUES (
    'Super Administrador',
    'superadmin@biometricip.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1,
    NULL,
    NOW(),
    NOW()
);

-- ------------------------------------------------------------
-- Empresa demo (opcional — eliminar en producción)
-- Sustituir valores antes de ejecutar
-- ------------------------------------------------------------
INSERT INTO `tbl_empresas` (`nombre`, `ruc`, `email`, `telefono`, `is_active`, `created_at`, `updated_at`)
VALUES (
    'Empresa Demo',
    NULL,
    'admin@demo.com',
    NULL,
    1,
    NOW(),
    NOW()
);

-- Registrar credenciales del tenant en la BD central
-- db_name debe coincidir con la BD que se creará para este tenant
INSERT INTO `tenants` (`empresa_id`, `db_name`, `db_user`, `db_pass`, `created_at`, `updated_at`)
VALUES (
    1,
    'biometricip_1',
    'root',       -- usuario MySQL del tenant
    '',           -- contraseña del usuario MySQL (vacía en dev con root)
    NOW(),
    NOW()
);

-- Admin de la empresa demo
-- Contraseña: demo123
INSERT INTO `users` (
    `name`, `email`, `password`, `role`, `is_active`,
    `empresa_id`, `codigo_empleado`, `cargo`, `created_at`, `updated_at`
)
VALUES (
    'Admin Demo',
    'admin@demo.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1,
    1,
    'ADM-0001',
    'Administrador',
    NOW(),
    NOW()
);

-- Luego crear la BD del tenant y su estructura:
--   CREATE DATABASE biometricip_tenant_1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE biometricip_tenant_1;
--   SOURCE 02_setup_tenant.sql;
-- O usar: php artisan tenant:create-structure 1

SET FOREIGN_KEY_CHECKS = 1;
