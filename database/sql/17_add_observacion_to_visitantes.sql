-- Agrega campo de observación general al registro de visitantes
-- Ejecutar en la base de datos tenant (biometricip_1, etc.)

ALTER TABLE tbl_visitantes
    ADD COLUMN observacion TEXT NULL DEFAULT NULL AFTER induccion_fecha;
