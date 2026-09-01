-- Historial de notificaciones push enviadas
-- Base: biometricip (principal)

CREATE TABLE IF NOT EXISTS `tbl_push_historial` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id`      BIGINT UNSIGNED NULL,
    `enviado_por`     BIGINT UNSIGNED NOT NULL COMMENT 'users.id del admin que envió',
    `titulo`          VARCHAR(255)    NOT NULL,
    `mensaje`         TEXT            NOT NULL,
    `tipo_destinatario` ENUM('all','lider','selected') NOT NULL DEFAULT 'all',
    `lider_id`        BIGINT UNSIGNED NULL COMMENT 'Filtro por líder',
    `user_ids`        JSON            NULL COMMENT 'Array de user_ids cuando tipo=selected',
    `total_enviados`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `total_exitosos`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `total_fallidos`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_empresa_id`  (`empresa_id`),
    KEY `idx_enviado_por` (`enviado_por`),
    KEY `idx_created_at`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar en tbl_admin_tenant (BD central, no se replica a tenants)
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_push_historial', 'Historial de notificaciones push enviadas (central)', 1, 0, 0, 1, 100, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
