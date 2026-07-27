-- ============================================================
-- BiometricIP — Índice en users.cedula
-- Ejecutar sobre: biometricip (central) y cada BD tenant (biometricip_1, etc.)
-- ============================================================

-- Evita error si el índice ya existe
ALTER TABLE `users`
    ADD INDEX `idx_users_cedula` (`cedula`);
