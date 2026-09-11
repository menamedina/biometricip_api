-- ============================================================
-- Script: 017_add_dispositivos_vaciar_permission.sql
-- Descripción: Agrega permiso dispositivos.vaciar a Spatie
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES ('dispositivos.vaciar', 'web', NOW(), NOW());

-- Asignar a Super Administrador y Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name = 'dispositivos.vaciar'
  AND r.name IN ('Super Administrador', 'Administrador');
