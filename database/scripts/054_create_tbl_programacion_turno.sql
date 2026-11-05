-- ============================================================
-- Script: 054_create_tbl_programacion_turno.sql
-- Descripcion: Crea la tabla de programacion semanal de turnos
--              por lider. Cada lider solo puede ver y modificar
--              su propia programacion (aislamiento por lider_id).
--              La tabla va en la BD tenant de cada empresa.
-- BD: biometricip_X (tenant)
-- ============================================================

CREATE TABLE IF NOT EXISTS `tbl_programacion_turno` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lider_id`       BIGINT UNSIGNED NOT NULL COMMENT 'Usuario lider que creo la programacion',
    `user_id`        BIGINT UNSIGNED NOT NULL COMMENT 'Empleado a quien se le asigna el nuevo turno',
    `horario_id`     BIGINT UNSIGNED NOT NULL COMMENT 'Nuevo horario asignado para la semana programada',
    `semana_inicio`  DATE            NOT NULL COMMENT 'Fecha del martes de inicio de la semana programada',
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lider_user_semana` (`lider_id`, `user_id`, `semana_inicio`),
    KEY `idx_semana_inicio` (`semana_inicio`),
    KEY `idx_lider_id` (`lider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_programacion_turno', 'Programacion semanal de turnos por lider', 0, 1, 0, 1, 100, NOW(), NOW());
