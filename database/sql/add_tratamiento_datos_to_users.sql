-- Agregar campos de tratamiento de datos a la tabla users
-- Ejecutar en la base de datos principal (biometricip) y en cada tenant (biometricip_1, etc.)

ALTER TABLE `users`
    ADD COLUMN `tratamiento_datos` TINYINT(1) NOT NULL DEFAULT 0 AFTER `otp_expires_at`,
    ADD COLUMN `tratamiento_datos_at` DATETIME NULL DEFAULT NULL AFTER `tratamiento_datos`;
