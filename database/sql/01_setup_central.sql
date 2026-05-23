-- ============================================================
-- BiometricIP — Solo tablas nuevas (si ya tienes la BD con Laravel)
-- Ejecutar sobre: biometricip
-- USE biometricip;
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
-- Agregar empresa_id y campos de empleado a users + FK
-- ------------------------------------------------------------
ALTER TABLE `users`
    ADD COLUMN `empresa_id`      BIGINT UNSIGNED NULL AFTER `is_active`,
    ADD COLUMN `codigo_empleado` VARCHAR(255)    NULL AFTER `empresa_id`,
    ADD COLUMN `departamento`    VARCHAR(255)    NULL AFTER `codigo_empleado`,
    ADD COLUMN `cargo`           VARCHAR(255)    NULL AFTER `departamento`,
    ADD COLUMN `telefono`        VARCHAR(20)     NULL AFTER `cargo`,
    ADD COLUMN `foto_url`        VARCHAR(255)    NULL AFTER `telefono`,
    ADD COLUMN `face_descriptor` JSON            NULL AFTER `foto_url`,
    ADD CONSTRAINT `fk_users_empresa`
        FOREIGN KEY (`empresa_id`) REFERENCES `tbl_empresas` (`id`)
        ON DELETE SET NULL,
    ADD UNIQUE KEY `uq_empresa_codigo` (`empresa_id`, `codigo_empleado`);

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

SET FOREIGN_KEY_CHECKS = 1;
