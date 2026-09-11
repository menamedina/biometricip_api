-- ============================================================
-- Script: 013_add_empresa_permissions.sql
-- Descripción: Agrega permisos de empresa (Mi Empresa) a Spatie
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

-- Insertar permisos
INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('empresa.ver',    'web', NOW(), NOW()),
    ('empresa.editar', 'web', NOW(), NOW());

-- Asignar a Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('empresa.ver', 'empresa.editar')
  AND r.name = 'Super Administrador';

-- Asignar a Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('empresa.ver', 'empresa.editar')
  AND r.name = 'Administrador';
