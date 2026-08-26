-- Agrega los campos centro_costo y ruta a la tabla users
-- Ejecutar en la base de datos principal (biometricip) y en cada tenant (biometricip_1, etc.)

ALTER TABLE users
    ADD COLUMN centro_costo VARCHAR(100) NULL AFTER telefono,
    ADD COLUMN ruta         VARCHAR(100) NULL AFTER centro_costo;
