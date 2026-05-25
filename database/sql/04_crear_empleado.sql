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
      'Bogotá',
      4.6840,
      -74.0960,
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




  CREATE TABLE IF NOT EXISTS `tbl_departamentos` (
      `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `nombre`      VARCHAR(100) NOT NULL UNIQUE,
      `descripcion` VARCHAR(255) NULL,
      `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
      `created_at`  TIMESTAMP NULL,
      `updated_at`  TIMESTAMP NULL,
      PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  CREATE TABLE IF NOT EXISTS `tbl_cargos` (
      `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `departamento_id`  BIGINT UNSIGNED NULL,
      `nombre`           VARCHAR(100) NOT NULL,
      `descripcion`      VARCHAR(255) NULL,
      `is_active`        TINYINT(1) NOT NULL DEFAULT 1,
      `created_at`       TIMESTAMP NULL,
      `updated_at`       TIMESTAMP NULL,
      PRIMARY KEY (`id`),
      CONSTRAINT `fk_cargos_depto` FOREIGN KEY (`departamento_id`) REFERENCES `tbl_departamentos` (`id`) ON DELETE SET NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- ══════════════════════════════════════════════════════════════
  -- 1. INSERTAR DEPARTAMENTOS (en la BD del tenant: biometricip_1)
  -- ══════════════════════════════════════════════════════════════
  INSERT INTO tbl_departamentos (nombre, descripcion, is_active, created_at, updated_at) VALUES
  ('Tecnología',         'Desarrollo de software e infraestructura TI',  1, NOW(), NOW()),
  ('Recursos Humanos',   'Gestión del talento humano y bienestar',        1, NOW(), NOW()),
  ('Contabilidad',       'Finanzas, nómina y contabilidad general',       1, NOW(), NOW()),
  ('Ventas',             'Gestión comercial y relación con clientes',     1, NOW(), NOW()),
  ('Marketing',          'Publicidad, marca y comunicaciones',            1, NOW(), NOW()),
  ('Operaciones',        'Logística, procesos y cadena de suministro',    1, NOW(), NOW()),
  ('Jurídico',           'Asuntos legales y cumplimiento normativo',      1, NOW(), NOW()),
  ('Gerencia',           'Alta dirección y estrategia corporativa',       1, NOW(), NOW()),
  ('Soporte',            'Atención al cliente y soporte técnico',         1, NOW(), NOW()),
  ('Compras',            'Adquisiciones, proveedores y contratos',        1, NOW(), NOW());



  INSERT INTO biometricip_1.tbl_cargos (nombre, descripcion, is_active, created_at, updated_at) VALUES
  ('Desarrollador Backend',    'Desarrollo de APIs y servicios web',          1, NOW(), NOW()),
  ('Desarrollador Frontend',   'Interfaces de usuario y experiencia UX',      1, NOW(), NOW()),
  ('DevOps / Infraestructura', 'Servidores, CI/CD y redes',                   1, NOW(), NOW()),
  ('Analista de RRHH',         'Reclutamiento, selección y onboarding',       1, NOW(), NOW()),
  ('Coordinador de Nómina',    'Liquidación y pago de nómina',                1, NOW(), NOW()),
  ('Contador General',         'Elaboración de estados financieros',          1, NOW(), NOW()),
  ('Auxiliar Contable',        'Registro de transacciones y conciliaciones',  1, NOW(), NOW()),
  ('Ejecutivo de Ventas',      'Captación y cierre de clientes',              1, NOW(), NOW()),
  ('Gerente General',          'Dirección y toma de decisiones estratégicas', 1, NOW(), NOW()),
  ('Técnico de Soporte',       'Resolución de incidentes nivel 1 y 2',        1, NOW(), NOW());
