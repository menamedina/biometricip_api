-- ============================================================
-- BiometricIP — Crear nueva empresa (tenant)
-- Ejecutar sobre: biometricip
--
-- Pasos manuales alternativos al comando:
--   php artisan tenant:create --nombre="..." --email="..." --admin-password="..."
--
-- Sustituir los valores entre <  > antes de ejecutar.
-- ============================================================

USE biometricip;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. Registrar empresa en BD central
-- ------------------------------------------------------------
INSERT INTO `tbl_empresas` (`nombre`, `ruc`, `email`, `telefono`, `is_active`, `created_at`, `updated_at`)
VALUES (
    '<Nombre de la Empresa>',   -- nombre
    '<RUC>',                    -- ruc  (NULL si no aplica)
    '<email@empresa.com>',      -- email
    '<000-000-0000>',           -- telefono (NULL si no aplica)
    1,
    NOW(),
    NOW()
);

-- Capturar el ID generado
SET @empresa_id = LAST_INSERT_ID();

-- ------------------------------------------------------------
-- 2. Registrar credenciales del tenant
--    db_name  = nombre de la BD que se creará para este tenant
--    db_user  = usuario MySQL del tenant (puede ser el mismo root en dev)
--    db_pass  = contraseña del usuario
-- ------------------------------------------------------------
INSERT INTO `tenants` (`empresa_id`, `db_name`, `db_user`, `db_pass`, `created_at`, `updated_at`)
VALUES (
    @empresa_id,
    CONCAT('biometricip_tenant_', @empresa_id),   -- nombre de la BD
    '<db_user>',                                   -- usuario MySQL
    '<db_pass>',                                   -- contraseña MySQL
    NOW(),
    NOW()
);

-- ------------------------------------------------------------
-- 3. Crear la BD del tenant
-- ------------------------------------------------------------
SET @db_name = CONCAT('biometricip_tenant_', @empresa_id);

-- MySQL no permite variables en CREATE DATABASE; ejecutar esta
-- línea sustituyendo el nombre manualmente o usar el comando Artisan:
--   php artisan tenant:create-db {empresa_id}
--
-- CREATE DATABASE `biometricip_tenant_1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Crear usuario administrador de la empresa en BD central
--    Contraseña: bcrypt de la contraseña deseada.
--    Generar con:  php artisan tinker --execute="echo bcrypt('password123');"
-- ------------------------------------------------------------
INSERT INTO `users` (
    `name`, `email`, `password`, `role`, `is_active`,
    `empresa_id`, `codigo_empleado`, `cargo`,
    `created_at`, `updated_at`
)
VALUES (
    '<Nombre Admin>',
    '<admin@empresa.com>',
    '<bcrypt_hash>',      -- hash generado con bcrypt
    'admin',
    1,
    @empresa_id,
    'ADM-0001',
    'Administrador',
    NOW(),
    NOW()
);

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 5. Crear estructura del tenant (tablas en la BD del tenant)
--    Ejecutar el script 02_setup_tenant.sql sobre la BD creada:
--
--    USE biometricip_tenant_1;
--    SOURCE 02_setup_tenant.sql;
--
-- O usar el comando Artisan:
--   php artisan tenant:create-structure {empresa_id}
-- ------------------------------------------------------------
