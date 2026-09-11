-- ============================================================
-- Script: 024_add_reportes_crear_editar_permissions.sql
-- Descripción: Agrega reportes.crear y reportes.editar
--              para gestión de registros en Resumen Marcación
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
    ('reportes.crear', 'web', NOW(), NOW()),
    ('reportes.editar', 'web', NOW(), NOW());

-- Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('reportes.crear', 'reportes.editar') AND r.name = 'Super Administrador';

-- Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('reportes.crear', 'reportes.editar') AND r.name = 'Administrador';
