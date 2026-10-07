-- ============================================================
-- Script: 051_move_tipos_permisos_to_configuracion.sql
-- Descripcion: Separa el permiso de acceso al catalogo de tipos
--              de ausencia de los permisos funcionales de ausencias.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES ('configuracion.tipos_permisos', 'web', NOW(), NOW());

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT nueva.id, anterior.role_id
FROM `role_has_permissions` anterior
JOIN `permissions` vieja ON vieja.id = anterior.permission_id
JOIN `permissions` nueva ON nueva.name = 'configuracion.tipos_permisos'
WHERE vieja.name = 'permisos.tipos';

DELETE anterior
FROM `role_has_permissions` anterior
JOIN `permissions` vieja ON vieja.id = anterior.permission_id
WHERE vieja.name = 'permisos.tipos';

DELETE FROM `permissions`
WHERE `name` = 'permisos.tipos';
