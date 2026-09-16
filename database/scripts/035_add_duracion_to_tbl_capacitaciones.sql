-- ============================================================
-- 035_add_duracion_to_tbl_capacitaciones.sql
-- Agrega campo duracion_horas a tbl_capacitaciones (BD tenant)
-- ============================================================

ALTER TABLE `tbl_capacitaciones`
    ADD COLUMN `duracion_horas` VARCHAR(20) NULL COMMENT 'Duración libre o en horas (ej: 2, 1.5, "4 horas")' AFTER `fecha_capacitacion`;
