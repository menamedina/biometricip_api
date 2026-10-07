-- ============================================================
-- Script: 052_create_tipos_permisos_actions.sql
-- Descripcion: Crea los permisos independientes del modulo
--              Tipos de Permiso: ver, crear, editar y eliminar.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES
    ('tipos_permisos.ver', 'web', NOW(), NOW()),
    ('tipos_permisos.crear', 'web', NOW(), NOW()),
    ('tipos_permisos.editar', 'web', NOW(), NOW()),
    ('tipos_permisos.eliminar', 'web', NOW(), NOW());

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT nueva.id, anterior.role_id
FROM `role_has_permissions` anterior
JOIN `permissions` vieja ON vieja.id = anterior.permission_id
JOIN `permissions` nueva ON nueva.name = 'tipos_permisos.ver'
WHERE vieja.name = 'configuracion.tipos_permisos';

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name IN ('tipos_permisos.ver', 'tipos_permisos.crear', 'tipos_permisos.editar', 'tipos_permisos.eliminar')
  AND r.name IN ('Super Administrador', 'Administrador');

DELETE rp
FROM `role_has_permissions` rp
JOIN `permissions` p ON p.id = rp.permission_id
WHERE p.name = 'configuracion.tipos_permisos';

DELETE FROM `permissions`
WHERE name = 'configuracion.tipos_permisos';

DELETE rp
FROM `role_has_permissions` rp
JOIN `permissions` p ON p.id = rp.permission_id
WHERE p.name = 'roles.tipos';

DELETE FROM `permissions`
WHERE name = 'roles.tipos';
