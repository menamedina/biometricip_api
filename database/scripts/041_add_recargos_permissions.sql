-- Agrega permisos para el módulo de Recargos
-- Ejecutar en la base de datos principal: biometricip

/*
 3 -SELECT p.name, r.name AS rol
       4 -  FROM role_has_permissions rhp
       5 -  JOIN permissions p ON p.id = rhp.permission_id
       6 -  JOIN roles r ON r.id = rhp.role_id
       7 -  WHERE p.name LIKE 'recargos.%';

*/

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES
    ('recargos.ver',        'web', NOW(), NOW()),
    ('recargos.configurar', 'web', NOW(), NOW()),
    ('recargos.calcular',   'web', NOW(), NOW());

-- Asignar los 3 permisos al rol Super Administrador
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p, `roles` r
WHERE p.name IN ('recargos.ver', 'recargos.configurar', 'recargos.calcular')
  AND r.name = 'Super Administrador';
