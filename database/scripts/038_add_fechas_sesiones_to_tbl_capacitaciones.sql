-- ============================================================
-- 038_add_fechas_sesiones_to_tbl_capacitaciones.sql
-- Agrega campo fechas_sesiones (JSON) para múltiples sesiones
-- Ejecutar en: biometricip_1, biometricip_2 (todos los tenants)
-- ============================================================

ALTER TABLE `tbl_capacitaciones`
    ADD COLUMN `fechas_sesiones` TEXT NULL COMMENT 'JSON array de fechas/sesiones adicionales' AFTER `fecha_capacitacion`;


  USE biometricip_1;
  ALTER TABLE tbl_capacitaciones DROP COLUMN IF EXISTS fechas_sesiones;

  -- biometricip_2
  USE biometricip_2;
  ALTER TABLE tbl_capacitaciones DROP COLUMN IF EXISTS fechas_sesiones;
