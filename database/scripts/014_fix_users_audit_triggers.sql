-- ============================================================
-- Script: 014_fix_users_audit_triggers.sql
-- Descripción: Recrea triggers de auditoría de users sin las
--              columnas legacy eliminadas en script 012
--              (exportar_empleados, importar_empleados,
--               crear_empleado, editar_empleado)
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

-- Trigger UPDATE
DROP TRIGGER `trg_users_update`;

DELIMITER $$

CREATE TRIGGER `trg_users_update`
AFTER UPDATE ON `users`
FOR EACH ROW
BEGIN
    INSERT INTO `tbl_users_log`
        (`user_id`, `evento`, `anterior`, `nuevo`, `changed_by`, `created_at`)
    VALUES (
        OLD.id,
        'UPDATE',
        JSON_OBJECT(
            'name',            OLD.name,
            'cedula',          OLD.cedula,
            'email',           OLD.email,
            'role',            OLD.role,
            'tipo',            OLD.tipo,
            'admin_tenant',    OLD.admin_tenant,
            'is_active',       OLD.is_active,
            'empresa_id',      OLD.empresa_id,
            'empleador_id',    OLD.empleador_id,
            'lider_id',        OLD.lider_id,
            'codigo_empleado', OLD.codigo_empleado,
            'departamento_id', OLD.departamento_id,
            'cargo_id',        OLD.cargo_id,
            'horario_id',      OLD.horario_id,
            'telefono',        OLD.telefono,
            'centro_costo',    OLD.centro_costo,
            'ruta',            OLD.ruta
        ),
        JSON_OBJECT(
            'name',            NEW.name,
            'cedula',          NEW.cedula,
            'email',           NEW.email,
            'role',            NEW.role,
            'tipo',            NEW.tipo,
            'admin_tenant',    NEW.admin_tenant,
            'is_active',       NEW.is_active,
            'empresa_id',      NEW.empresa_id,
            'empleador_id',    NEW.empleador_id,
            'lider_id',        NEW.lider_id,
            'codigo_empleado', NEW.codigo_empleado,
            'departamento_id', NEW.departamento_id,
            'cargo_id',        NEW.cargo_id,
            'horario_id',      NEW.horario_id,
            'telefono',        NEW.telefono,
            'centro_costo',    NEW.centro_costo,
            'ruta',            NEW.ruta
        ),
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;


-- Trigger DELETE
DROP TRIGGER `trg_users_delete`;

DELIMITER $$

CREATE TRIGGER `trg_users_delete`
AFTER DELETE ON `users`
FOR EACH ROW
BEGIN
    INSERT INTO `tbl_users_log`
        (`user_id`, `evento`, `anterior`, `nuevo`, `changed_by`, `created_at`)
    VALUES (
        OLD.id,
        'DELETE',
        JSON_OBJECT(
            'name',            OLD.name,
            'cedula',          OLD.cedula,
            'email',           OLD.email,
            'role',            OLD.role,
            'tipo',            OLD.tipo,
            'admin_tenant',    OLD.admin_tenant,
            'is_active',       OLD.is_active,
            'empresa_id',      OLD.empresa_id,
            'empleador_id',    OLD.empleador_id,
            'lider_id',        OLD.lider_id,
            'codigo_empleado', OLD.codigo_empleado,
            'departamento_id', OLD.departamento_id,
            'cargo_id',        OLD.cargo_id,
            'horario_id',      OLD.horario_id,
            'telefono',        OLD.telefono,
            'centro_costo',    OLD.centro_costo,
            'ruta',            OLD.ruta
        ),
        NULL,
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;
