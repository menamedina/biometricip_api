-- ============================================================
-- Script: 016_add_empleados_historial_permission.sql
-- Descripción: Agrega permiso empleados.historial a Spatie
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

-- Insertar permiso
INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES ('empleados.historial', 'web', NOW(), NOW());

-- Asignar a Super Administrador, Administrador y Supervisor
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name = 'empleados.historial'
  AND r.name IN ('Super Administrador', 'Administrador', 'Supervisor');
