-- ================================================
-- TABLA: tbl_admin_tenant
-- Configuración de tablas para generación de SQL tenant
-- Ejecutar en la BD central: biometricip
-- ================================================

USE biometricip;

CREATE TABLE IF NOT EXISTS `tbl_admin_tenant` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre_tabla`      VARCHAR(100)    NOT NULL UNIQUE,
    `descripcion`       VARCHAR(255)    NULL,
    `es_bd_central`     TINYINT(1)      NOT NULL DEFAULT 0,
    `copiar_estructura` TINYINT(1)      NOT NULL DEFAULT 1,
    `copiar_datos`      TINYINT(1)      NOT NULL DEFAULT 0,
    `activo`            TINYINT(1)      NOT NULL DEFAULT 1,
    `orden`             INT             NOT NULL DEFAULT 100,
    `created_at`        TIMESTAMP       NULL,
    `updated_at`        TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `tbl_admin_tenant`;

-- ── BD CENTRAL (no se copian al tenant) ──────────────────────────────────────
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('users',                  'Usuarios del sistema (central)',            1, 0, 0, 1,  1, NOW(), NOW()),
    ('personal_access_tokens', 'Tokens Sanctum (central)',                  1, 0, 0, 1,  2, NOW(), NOW()),
    ('tbl_empresas',           'Empresas / tenants registrados (central)',  1, 0, 0, 1,  3, NOW(), NOW()),
    ('tenants',                'Registro de BDs tenant (central)',          1, 0, 0, 1,  4, NOW(), NOW()),
    ('tbl_admin_tenant',       'Configuración de tablas tenant (central)',  1, 0, 0, 1,  5, NOW(), NOW()),
    ('sessions',               'Sesiones de usuario (central)',             1, 0, 0, 1,  6, NOW(), NOW()),
    ('cache',                  'Caché de Laravel (central)',                1, 0, 0, 1,  7, NOW(), NOW()),
    ('cache_locks',            'Locks de caché (central)',                  1, 0, 0, 1,  8, NOW(), NOW()),
    ('jobs',                   'Cola de trabajos (central)',                1, 0, 0, 1,  9, NOW(), NOW()),
    ('failed_jobs',            'Trabajos fallidos (central)',               1, 0, 0, 1, 10, NOW(), NOW()),
    ('job_batches',            'Lotes de trabajos (central)',               1, 0, 0, 1, 11, NOW(), NOW());

-- ── TABLAS TENANT — solo estructura (vacías) ─────────────────────────────────
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_sedes',                    'Sedes/oficinas de la empresa',                    0, 1, 0, 1, 10, NOW(), NOW()),
    ('tbl_registros_asistencia',     'Registros de marcación de asistencia',            0, 1, 0, 1, 20, NOW(), NOW()),
    ('tbl_fotos_asistencia',         'Fotos de evidencia de asistencia',                0, 1, 0, 1, 21, NOW(), NOW()),
    ('tbl_departamentos',            'Departamentos de la empresa',                     0, 1, 0, 1, 30, NOW(), NOW()),
    ('tbl_cargos',                   'Cargos / puestos de trabajo',                     0, 1, 0, 1, 31, NOW(), NOW()),
    ('tbl_horarios',                 'Horarios laborales',                              0, 1, 0, 1, 40, NOW(), NOW()),
    ('tbl_permisos',                 'Permisos de ausencia / llegada tarde',            0, 1, 0, 1, 50, NOW(), NOW()),
    ('tbl_festivos',                 'Días festivos',                                   0, 1, 0, 1, 60, NOW(), NOW()),
    ('tbl_imagenes_rostro_usuario',  'Imágenes de rostro para reconocimiento facial',   0, 1, 0, 1, 71, NOW(), NOW()),
    ('tbl_visitantes',               'Registros de visitantes',                         0, 1, 0, 1, 80, NOW(), NOW()),
    ('tbl_dispositivos_biometricos', 'Dispositivos biométricos ZKTeco',                 0, 1, 0, 1, 90, NOW(), NOW());

-- ── TABLAS TENANT — inactivas (existen en tenant pero no se exportan) ─────────
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_visitantes_imagenes', 'Imágenes de visitantes (no requerida en tenant)',  0, 1, 0, 0, 81, NOW(), NOW()),
    ('tbl_sync_logs',           'Logs de sincronización (no requerida en tenant)',  0, 1, 0, 0, 91, NOW(), NOW());
