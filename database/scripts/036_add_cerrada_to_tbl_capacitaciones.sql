-- ============================================================
-- 036_add_cerrada_to_tbl_capacitaciones.sql
-- Agrega estado manual abierta/cerrada a tbl_capacitaciones
-- Ejecutar en: biometricip_1, biometricip_2 (todos los tenants)
-- ============================================================

ALTER TABLE `tbl_capacitaciones`
    ADD COLUMN `cerrada` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Abierta, 1=Cerrada manualmente por el admin' AFTER `activo`;
