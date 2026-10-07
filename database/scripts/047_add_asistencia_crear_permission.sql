-- ============================================================
-- Script: 047_add_asistencia_crear_permission.sql
-- Descripcion: Agrega el permiso para crear registros manuales
--              dentro del modulo Registros.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES ('asistencia.crear', 'web', NOW(), NOW());

-- Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name = 'asistencia.crear'
  AND r.name = 'Super Administrador';

-- Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name = 'asistencia.crear'
  AND r.name = 'Administrador';
