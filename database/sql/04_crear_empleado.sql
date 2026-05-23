-- ============================================================
-- BiometricIP — Insertar empleados en la BD central
-- Ejecutar sobre: biometricip
--
-- Los empleados son usuarios con role = 'empleado'.
-- No existe tabla tbl_empleados — todo está en users.
--
-- Generar hash de contraseña:
--   php artisan tinker --execute="echo bcrypt('password123');"
-- ============================================================

USE biometricip;
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Admin empresa 1  |  pass: menamedina
-- ------------------------------------------------------------
INSERT INTO `users` (
    `name`, `email`, `password`, `role`, `is_active`,
    `empresa_id`, `codigo_empleado`, `cargo`, `created_at`, `updated_at`
)
VALUES (
    'Mena Medina',
    'menamedina@mail.com',
    '$2y$12$hquqjsXF/THO5ZgEUFlxAeJo/MxbWM1x3HTGXyktfZMfjMeUL7q.W',
    'admin',
    1,
    1,
    'ADM-0001',
    'Administrador',
    NOW(),
    NOW()
);

-- ------------------------------------------------------------
-- Empleados de la empresa 1 (biometricip_1)
-- Contraseña de ejemplo: empleado123
-- ------------------------------------------------------------
INSERT INTO `users` (
    `name`,
    `email`,
    `password`,
    `role`,
    `is_active`,
    `empresa_id`,
    `codigo_empleado`,
    `departamento`,
    `cargo`,
    `telefono`,
    `created_at`,
    `updated_at`
)
VALUES
(
    'Juan Pérez',
    'juan.perez@demo.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'empleado',
    1,
    1,
    'EMP-0001',
    'Sistemas',
    'Desarrollador',
    '999-000-001',
    NOW(),
    NOW()
),
(
    'María López',
    'maria.lopez@demo.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'empleado',
    1,
    1,
    'EMP-0002',
    'Recursos Humanos',
    'Analista RR.HH.',
    '999-000-002',
    NOW(),
    NOW()
),
(
    'Carlos Ramos',
    'carlos.ramos@demo.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'empleado',
    1,
    1,
    'EMP-0003',
    'Contabilidad',
    'Contador',
    '999-000-003',
    NOW(),
    NOW()
);




  INSERT INTO `tbl_sedes` (`codigo`, `nombre`, `direccion`, `lat`, `lng`, `radio_mts`, `secret_key`, `is_active`, `created_at`, `updated_at`)
  VALUES (
      'SEDE-001',
      'Sede Principal',
      'Av. Reforma 222, CDMX',
      19.4326077,
      -99.1331785,
      150,
      '4c31ddabc377c81fa57d666f361f48e0',
      1,
      NOW(),
      NOW()
  );
-- ------------------------------------------------------------
-- Verificar empleados insertados
-- ------------------------------------------------------------
SELECT id, name, email, role, empresa_id, codigo_empleado, departamento, cargo
FROM users
WHERE empresa_id = 1 AND role = 'empleado'
ORDER BY codigo_empleado;
