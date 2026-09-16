-- ============================================================
-- 037_add_empresa_id_to_tbl_capacitaciones.sql
-- Agrega empresa_id a tbl_capacitaciones para poder resolver
-- el tenant en rutas públicas (sin sesión activa).
-- Ejecutar en: biometricip_1, biometricip_2 (todos los tenants)
-- ============================================================

ALTER TABLE `tbl_capacitaciones`
    ADD COLUMN `empresa_id` INT NULL COMMENT 'ID de la empresa (para resolver tenant en rutas públicas)' AFTER `creado_por`;


cd biometricip_api && php artisan tinker \App\Models\Capacitacion::find(3)->update(['token' => \Illuminate\Support\Str::random(64)]);

