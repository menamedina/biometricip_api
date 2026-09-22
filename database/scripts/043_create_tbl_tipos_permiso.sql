-- ============================================================
-- 043_create_tbl_tipos_permiso.sql
-- Catálogo de tipos de permiso + ALTER tbl_permisos
-- Tabla en BD tenant + registro en tbl_admin_tenant
-- ============================================================

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 1: Tabla catálogo en BD TENANT (ejecutar en biometricip_1, etc.)
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tbl_tipos_permiso` (
    `id`             INT          NOT NULL AUTO_INCREMENT,
    `nombre`         VARCHAR(100) NOT NULL,
    `es_remunerado`  TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1 = permiso remunerado, 0 = no remunerado',
    `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP    NULL,
    `updated_at`     TIMESTAMP    NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 2: Datos semilla - Tipos de permiso por defecto
-- ─────────────────────────────────────────────────────────────

INSERT IGNORE INTO `tbl_tipos_permiso` (`nombre`, `es_remunerado`, `is_active`, `created_at`, `updated_at`)
VALUES
    ('Cita Médica',             1, 1, NOW(), NOW()),
    ('Vacaciones',              1, 1, NOW(), NOW()),
    ('Calamidad',               1, 1, NOW(), NOW()),
    ('Licencia de maternidad',  1, 1, NOW(), NOW()),
    ('Permiso no remunerado',   0, 1, NOW(), NOW()),
    ('Diligencia personal',     0, 1, NOW(), NOW()),
    ('Capacitación',            1, 1, NOW(), NOW()),
    ('Incapacidad',             1, 1, NOW(), NOW());

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 3: ALTER tbl_permisos - Agregar campos nuevos
-- Los campos viejos (tipo, fecha, horas_permiso) se mantienen
-- ─────────────────────────────────────────────────────────────

ALTER TABLE `tbl_permisos`
    ADD COLUMN `tipo_permiso_id` INT NULL AFTER `user_id`,
    ADD COLUMN `fecha_inicio` DATETIME NULL AFTER `fecha`,
    ADD COLUMN `fecha_fin` DATETIME NULL AFTER `fecha_inicio`;

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 4: Registrar tabla en tbl_admin_tenant (BD biometricip)
-- ─────────────────────────────────────────────────────────────

INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_tipos_permiso', 'Catálogo de tipos de permiso/ausencia', 0, 1, 1, 1, 130, NOW(), NOW());
