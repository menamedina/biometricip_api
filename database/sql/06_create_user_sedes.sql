-- ============================================================
-- BiometricIP — Tabla tbl_user_sedes
-- Ejecutar sobre: biometricip (base de datos central)
-- USE biometricip;
-- ============================================================
-- Propósito: Asignar a cada usuario una sede dentro de su empresa.
-- Nota: sede_id es referencia lógica (tbl_sedes vive en BD tenant).
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `tbl_user_sedes` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `empresa_id` BIGINT UNSIGNED NOT NULL,
    `sede_id`    BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP       NULL,
    `updated_at` TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_empresa` (`user_id`, `empresa_id`),
    INDEX `idx_user_sedes_user_id`    (`user_id`),
    INDEX `idx_user_sedes_empresa_id` (`empresa_id`),
    INDEX `idx_user_sedes_sede_id`    (`sede_id`),
    CONSTRAINT `fk_user_sedes_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_user_sedes_empresa`
        FOREIGN KEY (`empresa_id`) REFERENCES `tbl_empresas` (`id`)
        ON DELETE CASCADE
    -- sede_id NO tiene FK constraint porque tbl_sedes vive en BD tenant
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
