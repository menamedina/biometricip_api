-- ============================================================
-- BiometricIP — Eliminar columna sede_id de users
-- Ejecutar sobre: biometricip (base de datos central)
-- USE biometricip;
-- ============================================================
-- La sede del usuario ahora se gestiona en tbl_user_sedes
-- ============================================================

ALTER TABLE `users` DROP COLUMN `sede_id`;
