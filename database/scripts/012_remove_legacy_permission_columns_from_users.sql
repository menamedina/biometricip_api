-- ============================================================
-- Script: 012_remove_legacy_permission_columns_from_users.sql
-- Descripción: Elimina columnas de permisos legacy de la tabla users.
--              Estos permisos quedan reemplazados por Spatie:
--              exportar_empleados → empleados.exportar
--              importar_empleados → empleados.importar
--              crear_empleado     → empleados.crear
--              editar_empleado    → empleados.editar
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

ALTER TABLE `users`
    DROP COLUMN `exportar_empleados`,
    DROP COLUMN `importar_empleados`,
    DROP COLUMN `crear_empleado`,
    DROP COLUMN `editar_empleado`;
