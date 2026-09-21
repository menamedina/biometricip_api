-- ============================================================
-- 040_create_tbl_conceptos_recargo.sql
-- Módulo de Recargos según Normativa Colombiana (CST)
-- Tablas en BD tenant + registro en tbl_admin_tenant
-- ============================================================

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 1: Tablas en BD TENANT (ejecutar en biometricip_1, etc.)
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tbl_conceptos_recargo` (
    `id`            INT           NOT NULL AUTO_INCREMENT,
    `codigo`        VARCHAR(30)   NOT NULL COMMENT 'Código único del concepto (ej: NOCT_ORD)',
    `nombre`        VARCHAR(100)  NOT NULL,
    `porcentaje`    DECIMAL(6,2)  NOT NULL COMMENT 'Porcentaje adicional sobre hora ordinaria',
    `es_extra`      TINYINT(1)    NOT NULL DEFAULT 0 COMMENT '1 si es concepto de hora extra',
    `es_nocturno`   TINYINT(1)    NOT NULL DEFAULT 0 COMMENT '1 si aplica en jornada nocturna (7pm-6am)',
    `es_festivo`    TINYINT(1)    NOT NULL DEFAULT 0 COMMENT '1 si aplica en domingo/festivo',
    `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP     NULL,
    `updated_at`    TIMESTAMP     NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_conceptos_recargo_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_recargos_calculados` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`             BIGINT UNSIGNED NOT NULL,
    `fecha`               DATE            NOT NULL,
    `horario_id`          BIGINT UNSIGNED NULL,
    `concepto_recargo_id` INT             NOT NULL,
    `minutos`             DECIMAL(8,2)    NOT NULL DEFAULT 0,
    `created_at`          TIMESTAMP       NULL,
    `updated_at`          TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_fecha_concepto` (`user_id`, `fecha`, `concepto_recargo_id`),
    CONSTRAINT `fk_recargos_concepto`
        FOREIGN KEY (`concepto_recargo_id`) REFERENCES `tbl_conceptos_recargo` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 2: Datos semilla - Conceptos CST colombiano
-- ─────────────────────────────────────────────────────────────

INSERT INTO `tbl_conceptos_recargo` (`codigo`, `nombre`, `porcentaje`, `es_extra`, `es_nocturno`, `es_festivo`, `is_active`, `created_at`, `updated_at`)
VALUES
    ('NOCT_ORD',       'Recargo nocturno ordinario',           35.00,  0, 1, 0, 1, NOW(), NOW()),
    ('DOM_FEST_DIUR',  'Recargo dominical/festivo diurno',     90.00,  0, 0, 1, 1, NOW(), NOW()),
    ('NOCT_DOM',       'Recargo nocturno dominical',          125.00,  0, 1, 1, 1, NOW(), NOW()),
    ('EXTRA_DIUR',     'Hora extra diurna',                   125.00,  1, 0, 0, 1, NOW(), NOW()),
    ('EXTRA_NOCT',     'Hora extra nocturna',                 175.00,  1, 1, 0, 1, NOW(), NOW()),
    ('EXTRA_DIUR_DOM', 'H. extra diurna dominical/festiva',   215.00,  1, 0, 1, 1, NOW(), NOW()),
    ('EXTRA_NOCT_DOM', 'Extra nocturna dominical',            265.00,  1, 1, 1, 1, NOW(), NOW()),
    ('FEST_DIUR',      'Hora festiva diurna',                 190.00,  0, 0, 1, 1, NOW(), NOW()),
    ('FEST_NOCT',      'Hora festiva nocturna',               225.00,  0, 1, 1, 1, NOW(), NOW());

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 3: Registrar tablas en tbl_admin_tenant (BD biometricip)
-- ─────────────────────────────────────────────────────────────

INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_conceptos_recargo',   'Conceptos de recargo laboral según normativa colombiana (CST)', 0, 1, 1, 1, 120, NOW(), NOW()),
    ('tbl_recargos_calculados', 'Recargos calculados por empleado, fecha y concepto',            0, 1, 0, 1, 121, NOW(), NOW());


  USE biometricip_1;
  SHOW TABLES LIKE 'tbl_conceptos_recargo';
  SHOW TABLES LIKE 'tbl_recargos_calculados';
  SELECT COUNT(*) FROM tbl_conceptos_recargo;  -- debe dar 9
