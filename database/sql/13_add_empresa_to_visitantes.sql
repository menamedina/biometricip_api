-- ============================================================
-- BiometricIP — Agrega columna empresa a tbl_visitantes
-- Ejecutar sobre cada BD tenant: biometricip_1, etc.
-- ============================================================

ALTER TABLE `tbl_visitantes`
    ADD COLUMN `empresa` VARCHAR(255) NULL DEFAULT NULL AFTER `arl`;

ALTER TABLE `tbl_visitantes`
    ADD COLUMN `placa` VARCHAR(20) NULL DEFAULT NULL AFTER `empresa`;

  ALTER TABLE `tbl_visitantes`
      ADD INDEX `idx_visitantes_cedula` (`cedula`);


        ALTER TABLE `users`
      ADD INDEX `idx_users_cedula` (`cedula`);
