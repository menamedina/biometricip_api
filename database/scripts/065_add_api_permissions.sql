-- 065: Permisos para sección API Tokens
-- Fecha: 2026-11-28

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('api.ver',       'web', NOW(), NOW()),
    ('api.gestionar', 'web', NOW(), NOW());

-- Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('api.ver','api.gestionar') AND r.name = 'Super Administrador';

-- Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('api.ver','api.gestionar') AND r.name = 'Administrador';
