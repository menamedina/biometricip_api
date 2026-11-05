-- ============================================================
-- Script: 055_add_programacion_turno_permissions.sql
-- Descripcion: Agrega los permisos del modulo Programacion
--              de Turno y los asigna a los roles Admin y
--              Super Administrador.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES
    ('programacion_turno.ver',      'web', NOW(), NOW()),
    ('programacion_turno.crear',    'web', NOW(), NOW()),
    ('programacion_turno.eliminar', 'web', NOW(), NOW());

-- Asignar todos los permisos a Super Administrador y Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name IN ('programacion_turno.ver', 'programacion_turno.crear', 'programacion_turno.eliminar')
  AND r.name IN ('Super Administrador', 'Administrador');

-- Asignar ver y crear al Supervisor (líder de equipo)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name IN ('programacion_turno.ver', 'programacion_turno.crear', 'programacion_turno.eliminar')
  AND r.name = 'Supervisor';
