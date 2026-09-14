-- ============================================================
-- Script: 031_add_doble_registro_to_sedes.sql
-- Descripción: Agrega columna doble_registro a tbl_sedes.
--              Si está activa, al escanear QR en esa sede se
--              genera automáticamente salida de la sede anterior
--              + entrada a la sede actual (y viceversa al regresar).
-- BD: tenant (biometricip_1, biometricip_2, ...)
-- Ejecutar en CADA base de datos tenant.
-- ============================================================

ALTER TABLE `tbl_sedes`
    ADD COLUMN `doble_registro` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'Si 1, al registrar asistencia aquí se crea salida de la sede anterior + entrada a esta (doble registro automático)'
    AFTER `is_active`;

-- Registrar en tbl_admin_tenant (BD central)
-- INSERT INTO `biometricip`.`tbl_admin_tenant`
--     (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
-- VALUES
--     ('tbl_sedes', 'Sedes / geocercas con soporte de doble registro', 0, 1, 0, 1, 100, NOW(), NOW())
-- ON DUPLICATE KEY UPDATE `descripcion` = VALUES(`descripcion`), `updated_at` = NOW();
