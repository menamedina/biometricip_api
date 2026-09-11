-- ============================================================
-- Script: 025_fix_super_admin_all_permissions.sql
-- Descripción: Asegura que Super Administrador tenga TODOS
--              los permisos existentes en la tabla permissions
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

-- Insertar todos los permisos que le falten al Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
CROSS JOIN `roles` r
WHERE r.name = 'Super Administrador';
