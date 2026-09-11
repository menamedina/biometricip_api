-- ============================================================
-- Script: 023_add_asistencia_foto_permission.sql
-- Descripción: Agrega permiso asistencia.foto (ver foto de evidencia)
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('asistencia.foto', 'web', NOW(), NOW());

-- Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name = 'asistencia.foto' AND r.name = 'Super Administrador';

-- Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name = 'asistencia.foto' AND r.name = 'Administrador';

-- Supervisor
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name = 'asistencia.foto' AND r.name = 'Supervisor';
