-- ============================================================
-- Script: 032_add_qr_doble_token_to_sedes.sql
-- Descripción: Agrega columna qr_doble_token a tbl_sedes para
--              el QR estático de doble registro (imprimible).
--              NULL = deshabilitado. Regenerar para invalidar QRs impresos.
-- BD: tenant (ejecutar en cada biometricip_N)
-- ============================================================

ALTER TABLE `tbl_sedes`
    ADD COLUMN `qr_doble_token` VARCHAR(64) NULL DEFAULT NULL
    COMMENT 'Token para QR estático de doble registro. NULL = deshabilitado. Regenerar para invalidar QRs impresos.'
    AFTER `qr_v3_token`;
