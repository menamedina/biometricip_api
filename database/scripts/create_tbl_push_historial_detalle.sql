-- Detalle de destinatarios por notificación push enviada
-- Base: biometricip (principal)

CREATE TABLE IF NOT EXISTS `tbl_push_historial_detalle` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `historial_id` BIGINT UNSIGNED NOT NULL COMMENT 'tbl_push_historial.id',
    `user_id`      BIGINT UNSIGNED NOT NULL COMMENT 'users.id del destinatario',
    `exitoso`      TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_historial_id` (`historial_id`),
    KEY `idx_user_id`      (`user_id`),
    CONSTRAINT `fk_detalle_historial` FOREIGN KEY (`historial_id`) REFERENCES `tbl_push_historial` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar en tbl_admin_tenant (BD central, no se replica a tenants)
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_push_historial_detalle', 'Detalle de destinatarios por notificación push (central)', 1, 0, 0, 1, 101, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
