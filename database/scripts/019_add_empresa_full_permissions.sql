-- ============================================================
-- Script: 019_add_empresa_full_permissions.sql
-- Descripción: Agrega permisos completos del módulo empresa
--              (empresa.crear, empresa.eliminar, empresa.token)
-- BD: biometricip (central)
-- este depende del tenant
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('empresa.crear',   'web', NOW(), NOW()),
    ('empresa.eliminar','web', NOW(), NOW()),
    ('empresa.token',   'web', NOW(), NOW());

-- Asignar a Super Administrador (todos)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('empresa.crear', 'empresa.eliminar', 'empresa.token')
  AND r.name = 'Super Administrador';

-- Asignar a Administrador (todos)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('empresa.crear', 'empresa.eliminar', 'empresa.token')
  AND r.name = 'Administrador';
