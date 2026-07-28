-- Agrega campo de observación general al registro de asistencia manual
-- Ejecutar en la base de datos tenant (biometricip_1, etc.)

ALTER TABLE tbl_registros_asistencia
    ADD COLUMN observacion TEXT NULL DEFAULT NULL AFTER metodo;
