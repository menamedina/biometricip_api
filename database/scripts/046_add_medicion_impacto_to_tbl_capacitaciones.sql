-- ============================================================
-- 046_add_medicion_impacto_to_tbl_capacitaciones.sql
-- Agrega metodología e información de medición de impacto
-- Ejecutar en: biometricip_1, biometricip_2 (todos los tenants)
-- ============================================================

ALTER TABLE `tbl_capacitaciones`
    ADD COLUMN `metodologia`          TEXT         NULL AFTER `temas`,
    ADD COLUMN `impacto_medible`      TINYINT(1)   NOT NULL DEFAULT 0 AFTER `metodologia`,
    ADD COLUMN `indicador_nombre`     VARCHAR(255) NULL AFTER `impacto_medible`,
    ADD COLUMN `formula_indicador`    TEXT         NULL AFTER `indicador_nombre`,
    ADD COLUMN `frecuencia_medicion`  VARCHAR(100) NULL AFTER `formula_indicador`;
