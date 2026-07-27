-- ============================================================
-- BiometricIP — Agrega user_id a tbl_visitantes
-- 0 = registro por QR web (público), >0 = registro manual por admin
-- Ejecutar sobre cada BD tenant: biometricip_1, etc.
-- ============================================================

ALTER TABLE `tbl_visitantes`
    ADD COLUMN `user_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `sede_id`;
