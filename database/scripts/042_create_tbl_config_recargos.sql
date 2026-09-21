-- ============================================================
-- 042_create_tbl_config_recargos.sql
-- Configuración de parámetros de recargos (clave-valor)
-- Tabla en BD tenant
-- ============================================================

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 1: Tabla en BD TENANT (ejecutar en biometricip_1, etc.)
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tbl_config_recargos` (
    `id`          INT          NOT NULL AUTO_INCREMENT,
    `clave`       VARCHAR(50)  NOT NULL,
    `valor`       VARCHAR(100) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    `created_at`  TIMESTAMP    NULL,
    `updated_at`  TIMESTAMP    NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_config_recargos_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 2: Datos semilla
-- ─────────────────────────────────────────────────────────────

INSERT IGNORE INTO `tbl_config_recargos` (`clave`, `valor`, `descripcion`, `created_at`, `updated_at`)
VALUES
    ('hora_inicio_nocturna', '19', 'Hora de inicio de jornada nocturna (CST: 19 = 7:00 PM)', NOW(), NOW()),
    ('hora_fin_nocturna',    '6',  'Hora de fin de jornada nocturna (CST: 6 = 6:00 AM)',     NOW(), NOW());

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 3: Registro en tbl_admin_tenant (ejecutar en biometricip)
-- ─────────────────────────────────────────────────────────────

INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_config_recargos', 'Configuración de parámetros de recargos (clave-valor)', 0, 1, 1, 1, 122, NOW(), NOW());
