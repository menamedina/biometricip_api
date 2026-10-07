-- ============================================================
-- Script: 050_move_tipos_permiso_permission.sql
-- Descripcion: Mueve el permiso del catalogo Tipos de Permisos
--              al modulo correcto Permisos / Ausencias.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES ('permisos.tipos', 'web', NOW(), NOW());

-- Conservar la asignacion de cualquier rol que tuviera el permiso anterior.
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT nueva.id, anterior.role_id
FROM `role_has_permissions` anterior
JOIN `permissions` vieja ON vieja.id = anterior.permission_id
JOIN `permissions` nueva ON nueva.name = 'permisos.tipos'
WHERE vieja.name = 'roles.tipos';

-- Eliminar el permiso anterior para que no aparezca bajo Roles y Permisos.
DELETE anterior
FROM `role_has_permissions` anterior
JOIN `permissions` vieja ON vieja.id = anterior.permission_id
WHERE vieja.name = 'roles.tipos';

DELETE FROM `permissions`
WHERE `name` = 'roles.tipos';
