-- ================================================
-- TABLA: tbl_empleador
-- Empleadores dentro de un tenant (puede haber varios por empresa)
-- Ejecutar en la BD TENANT: biometricip_1 (y cada tenant)
-- ================================================

CREATE TABLE IF NOT EXISTS `tbl_empleador` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(150)    NOT NULL,
    `descripcion` VARCHAR(255)    NULL,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP       NULL,
    `updated_at`  TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_empleador_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- COLUMNA: empleador_id en users (BD central: biometricip)
-- ================================================

ALTER TABLE `users`
    ADD COLUMN `empleador_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `empresa_id`;

-- ================================================
-- INSERT en tbl_admin_tenant (BD central: biometricip)
-- ================================================

INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_empleador', 'Empleadores dentro del tenant', 0, 1, 0, 1, 32, NOW(), NOW());
