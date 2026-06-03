-- ============================================================
-- BiometricIP — Agregar cédula a tabla users (BD central)
-- Ejecutar sobre: biometricip
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN `cedula` VARCHAR(20) NULL AFTER `name`;
