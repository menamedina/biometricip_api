-- ============================================================
-- Script: 027_create_tbl_ai_config.sql
-- Descripción: Tabla de configuración del asistente IA
--              por empresa. Almacena proveedor, modelo y
--              API key cifrada.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

CREATE TABLE IF NOT EXISTS `tbl_ai_config` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `empresa_id`    INT UNSIGNED     NULL     DEFAULT NULL COMMENT 'NULL = configuración global (admin_tenant)',
    `proveedor`     ENUM('openai','anthropic','deepseek','glm') NOT NULL DEFAULT 'anthropic',
    `modelo`        VARCHAR(100)     NOT NULL DEFAULT 'claude-haiku-4-5-20251001',
    `api_key`       TEXT             NOT NULL COMMENT 'API key cifrada con Laravel Crypt',
    `system_prompt` TEXT             NULL     DEFAULT NULL,
    `activo`        TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP        NULL     DEFAULT NULL,
    `updated_at`    TIMESTAMP        NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ai_config_empresa` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permisos IA
INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('ia.ver',        'web', NOW(), NOW()),
    ('ia.configurar', 'web', NOW(), NOW());

-- Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('ia.ver','ia.configurar') AND r.name = 'Super Administrador';

-- Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('ia.ver','ia.configurar') AND r.name = 'Administrador';

INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_ai_config', 'Configuración del asistente IA por empresa', 1, 0, 0, 1, 200, NOW(), NOW());
