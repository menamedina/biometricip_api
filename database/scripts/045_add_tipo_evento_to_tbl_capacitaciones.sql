-- ============================================================
-- 045_add_tipo_evento_to_tbl_capacitaciones.sql
-- Permite clasificar el evento como capacitación o asistencia
-- Ejecutar en: biometricip_1, biometricip_2 (todos los tenants)
-- ============================================================

ALTER TABLE `tbl_capacitaciones`
    ADD COLUMN `tipo_evento` VARCHAR(20) NOT NULL DEFAULT 'capacitacion'
    COMMENT 'capacitacion|asistencia'
    AFTER `titulo`;
