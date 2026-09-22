-- ============================================================
-- 044_add_creado_por_to_tbl_permisos.sql
-- Agrega campo creado_por a tbl_permisos
-- Ejecutar en BD tenant (biometricip_1, etc.)
-- ============================================================

ALTER TABLE `tbl_permisos`
    ADD COLUMN `creado_por` BIGINT UNSIGNED NULL AFTER `aprobado_por`;
