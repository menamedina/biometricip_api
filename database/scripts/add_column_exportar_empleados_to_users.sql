-- Agregar campos de permisos de gestión de empleados a la tabla users (BD tenant)
-- Booleanos, default 0
ALTER TABLE `users`
ADD COLUMN `exportar_empleados` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`,
ADD COLUMN `importar_empleados` TINYINT(1) NOT NULL DEFAULT 0 AFTER `exportar_empleados`,
ADD COLUMN `crear_empleado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `importar_empleados`,
ADD COLUMN `editar_empleado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `crear_empleado`;
