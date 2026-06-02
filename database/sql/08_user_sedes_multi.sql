-- ============================================================
-- BiometricIP — Soporte múltiples sedes por usuario
-- Ejecutar sobre: biometricip (base de datos central)
-- USE biometricip;
-- ============================================================

ALTER TABLE `tbl_user_sedes`
    DROP INDEX `uq_user_empresa`,
    ADD UNIQUE KEY `uq_user_sede` (`user_id`, `sede_id`);
