-- ============================================================
-- Script: 026_assign_empleado_role_to_users.sql
-- Descripción: Asigna el rol Spatie "Empleado" a todos los
--              usuarios que aún no tienen ningún rol Spatie,
--              excepto los que ya tienen "Super Administrador"
--              o "Administrador"
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

-- Insertar rol Empleado a usuarios que no tienen ningún rol Spatie asignado
INSERT IGNORE INTO `model_has_roles` (`role_id`, `model_type`, `model_id`)
SELECT
    r.id,
    'App\\Models\\User',
    u.id
FROM `users` u
CROSS JOIN `roles` r
WHERE r.name = 'Empleado'
  AND u.id NOT IN (
      SELECT model_id
      FROM `model_has_roles`
      WHERE model_type = 'App\\Models\\User'
  );
