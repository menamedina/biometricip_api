-- ============================================================
-- Script: 049_add_roles_tipos_permission.sql
-- Descripcion: Agrega el permiso para acceder al catalogo
--              Tipos de Permiso desde el menu de Roles y Permisos.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES ('roles.tipos', 'web', NOW(), NOW());

-- Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name = 'roles.tipos'
  AND r.name = 'Super Administrador';

-- Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name = 'roles.tipos'
  AND r.name = 'Administrador';
