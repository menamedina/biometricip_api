-- ============================================================
-- Script: 058_add_novedades_nomina_permissions.sql
-- Descripcion: Agrega los permisos del modulo Novedades de
--              Nomina y los asigna a los roles correspondientes.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES
    ('novedades_nomina.ver',      'web', NOW(), NOW()),
    ('novedades_nomina.exportar', 'web', NOW(), NOW());

-- Super Administrador y Administrador: acceso completo
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name IN ('novedades_nomina.ver', 'novedades_nomina.exportar')
  AND r.name IN ('Super Administrador', 'Administrador');

-- Supervisor: solo ver (sin exportar)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE p.name = 'novedades_nomina.ver'
  AND r.name = 'Supervisor';
