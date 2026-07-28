-- Agrega campo de observación a la inducción de visitantes
-- Ejecutar en la base de datos tenant (biometricip_1, etc.)

ALTER TABLE tbl_visitantes
    ADD COLUMN induccion_observacion TEXT NULL DEFAULT NULL AFTER induccion_fecha;
