-- Agrega permisos para el módulo Resumen Mensual
-- Ejecutar en la base de datos principal: biometricip

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES
    ('resumen_mensual.ver',      'web', NOW(), NOW()),
    ('resumen_mensual.exportar', 'web', NOW(), NOW());
