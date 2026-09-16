-- ============================================================
-- 039_modify_tbl_capacitacion_asistentes.sql
-- Agrega cédula, estado y fecha_confirmacion a asistentes
-- Ejecutar en: biometricip_1, biometricip_2 (todos los tenants)
-- ============================================================

ALTER TABLE `tbl_capacitacion_asistentes`
    ADD COLUMN `cedula`             VARCHAR(20)  NULL    COMMENT 'Cédula del participante'             AFTER `capacitacion_id`,
    ADD COLUMN `estado`             VARCHAR(20)  NOT NULL DEFAULT 'programado' COMMENT 'programado|confirmado' AFTER `telefono`,
    ADD COLUMN `fecha_confirmacion` TIMESTAMP    NULL    COMMENT 'Fecha en que confirmó asistencia'    AFTER `estado`;


