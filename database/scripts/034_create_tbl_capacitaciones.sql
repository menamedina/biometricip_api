-- ============================================================
-- 034_create_tbl_capacitaciones.sql
-- Módulo de Capacitaciones
-- Tablas en BD tenant + permisos Spatie en BD principal
-- ============================================================

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 1: Tablas en BD TENANT (ejecutar en biometricip_1, etc.)
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `tbl_capacitaciones` (
    `id`                   INT           NOT NULL AUTO_INCREMENT,
    `titulo`               VARCHAR(255)  NOT NULL,
    `temas`                TEXT          NULL     COMMENT 'JSON array de temas',
    `observaciones`        TEXT          NULL,
    `instructor_nombre`    VARCHAR(255)  NULL,
    `fecha_capacitacion`   DATETIME      NULL,
    `token`                VARCHAR(64)   NOT NULL,
    `expira_en`            INT           NOT NULL DEFAULT 60 COMMENT 'Duración en minutos',
    `fecha_expiracion`     DATETIME      NOT NULL,
    `activo`               TINYINT(1)   NOT NULL DEFAULT 1,
    `creado_por`           INT           NULL     COMMENT 'ID del usuario que creó la capacitación',
    `created_at`           TIMESTAMP     NULL,
    `updated_at`           TIMESTAMP     NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tbl_capacitaciones_token_unique` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_capacitacion_asistentes` (
    `id`                   INT           NOT NULL AUTO_INCREMENT,
    `capacitacion_id`      INT           NOT NULL,
    `nombre`               VARCHAR(255)  NOT NULL,
    `correo`               VARCHAR(255)  NOT NULL,
    `telefono`             VARCHAR(50)   NULL,
    `ip_registro`          VARCHAR(45)   NULL,
    `created_at`           TIMESTAMP     NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_cap_asistentes_capacitacion`
        FOREIGN KEY (`capacitacion_id`) REFERENCES `tbl_capacitaciones` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar tablas en tbl_admin_tenant (BD biometricip)
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_capacitaciones',          'Sesiones de capacitación con link de registro público', 0, 1, 0, 1, 110, NOW(), NOW()),
    ('tbl_capacitacion_asistentes', 'Asistentes registrados a capacitaciones',               0, 1, 0, 1, 111, NOW(), NOW());

-- ─────────────────────────────────────────────────────────────
-- SECCIÓN 2: Permisos Spatie en BD PRINCIPAL (biometricip)
-- ─────────────────────────────────────────────────────────────

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('capacitaciones.ver',      'web', NOW(), NOW()),
    ('capacitaciones.crear',    'web', NOW(), NOW()),
    ('capacitaciones.editar',   'web', NOW(), NOW()),
    ('capacitaciones.eliminar', 'web', NOW(), NOW());

-- Asignar todos los permisos de capacitaciones al rol admin
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
CROSS JOIN `roles` r
WHERE p.name LIKE 'capacitaciones.%'
  AND r.name = 'admin'
  AND NOT EXISTS (
      SELECT 1 FROM `role_has_permissions` rhp
      WHERE rhp.permission_id = p.id AND rhp.role_id = r.id
  );
