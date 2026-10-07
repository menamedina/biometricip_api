-- ============================================================
-- Script: 048_add_roles_permissions_tipos_permiso.sql
-- Descripcion: Garantiza los permisos para consultar y administrar
--              el catalogo de Tipos de Permiso.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES
    ('roles.ver', 'web', NOW(), NOW()),
    ('roles.editar', 'web', NOW(), NOW());

-- Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name IN ('roles.ver', 'roles.editar')
  AND r.name = 'Super Administrador';

-- Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name IN ('roles.ver', 'roles.editar')
  AND r.name = 'Administrador';
