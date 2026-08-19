-- ============================================================
-- BiometricIP — Audit log de users
-- BD CENTRAL: biometricip
-- No aplica para fotos ni campos sensibles (password, face_descriptor, etc.)
-- El usuario se pasa desde la app con: SET @audit_user_id = ?;
-- ============================================================

-- 1. Tabla de log
CREATE TABLE IF NOT EXISTS `tbl_users_log` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `evento`     ENUM('UPDATE', 'DELETE') NOT NULL,
    `anterior`   JSON NULL,
    `nuevo`      JSON NULL,
    `changed_by` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_ulog_user`   (`user_id`),
    INDEX `idx_ulog_evento` (`evento`),
    INDEX `idx_ulog_fecha`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. Trigger UPDATE
DROP TRIGGER IF EXISTS `trg_users_update`;

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
            'name',             OLD.name,
            'cedula',           OLD.cedula,
            'email',            OLD.email,
            'role',             OLD.role,
            'tipo',             OLD.tipo,
            'admin_tenant',     OLD.admin_tenant,
            'is_active',        OLD.is_active,
            'empresa_id',       OLD.empresa_id,
            'empleador_id',     OLD.empleador_id,
            'lider_id',         OLD.lider_id,
            'codigo_empleado',  OLD.codigo_empleado,
            'departamento_id',  OLD.departamento_id,
            'cargo_id',         OLD.cargo_id,
            'horario_id',       OLD.horario_id,
            'telefono',         OLD.telefono
        ),
        JSON_OBJECT(
            'name',             NEW.name,
            'cedula',           NEW.cedula,
            'email',            NEW.email,
            'role',             NEW.role,
            'tipo',             NEW.tipo,
            'admin_tenant',     NEW.admin_tenant,
            'is_active',        NEW.is_active,
            'empresa_id',       NEW.empresa_id,
            'empleador_id',     NEW.empleador_id,
            'lider_id',         NEW.lider_id,
            'codigo_empleado',  NEW.codigo_empleado,
            'departamento_id',  NEW.departamento_id,
            'cargo_id',         NEW.cargo_id,
            'horario_id',       NEW.horario_id,
            'telefono',         NEW.telefono
        ),
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;


-- 3. Trigger DELETE
DROP TRIGGER IF EXISTS `trg_users_delete`;

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
            'name',             OLD.name,
            'cedula',           OLD.cedula,
            'email',            OLD.email,
            'role',             OLD.role,
            'tipo',             OLD.tipo,
            'admin_tenant',     OLD.admin_tenant,
            'is_active',        OLD.is_active,
            'empresa_id',       OLD.empresa_id,
            'empleador_id',     OLD.empleador_id,
            'lider_id',         OLD.lider_id,
            'codigo_empleado',  OLD.codigo_empleado,
            'departamento_id',  OLD.departamento_id,
            'cargo_id',         OLD.cargo_id,
            'horario_id',       OLD.horario_id,
            'telefono',         OLD.telefono
        ),
        NULL,
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;
