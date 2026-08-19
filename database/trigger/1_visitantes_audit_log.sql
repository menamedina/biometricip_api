-- ============================================================
-- BiometricIP — Audit log de visitantes
-- Tabla + triggers para registrar UPDATE y DELETE
-- El usuario se pasa desde la app con: SET @audit_user_id = ?;
-- Ejecutar sobre cada BD tenant: biometricip_1, etc.
-- ============================================================

-- 1. Tabla de log
CREATE TABLE IF NOT EXISTS `tbl_visitantes_log` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `visitante_id`   BIGINT UNSIGNED NOT NULL,
    `evento`         ENUM('UPDATE', 'DELETE') NOT NULL,
    `anterior`       JSON NULL,
    `nuevo`          JSON NULL,
    `user_id`        INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_vlog_visitante` (`visitante_id`),
    INDEX `idx_vlog_evento`    (`evento`),
    INDEX `idx_vlog_fecha`     (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. Trigger UPDATE
DROP TRIGGER IF EXISTS `trg_visitantes_update`;

DELIMITER $$

CREATE TRIGGER `trg_visitantes_update`
AFTER UPDATE ON `tbl_visitantes`
FOR EACH ROW
BEGIN
    INSERT INTO `tbl_visitantes_log`
        (`visitante_id`, `evento`, `anterior`, `nuevo`, `user_id`, `created_at`)
    VALUES (
        OLD.id,
        'UPDATE',
        JSON_OBJECT(
            'sede_id',           OLD.sede_id,
            'user_id',           OLD.user_id,
            'nombre',            OLD.nombre,
            'cedula',            OLD.cedula,
            'telefono',          OLD.telefono,
            'eps',               OLD.eps,
            'arl',               OLD.arl,
            'empresa',           OLD.empresa,
            'placa',             OLD.placa,
            'persona_visita',    OLD.persona_visita,
            'hora_entrada',      DATE_FORMAT(OLD.hora_entrada, '%Y-%m-%d %H:%i:%s'),
            'hora_salida',       DATE_FORMAT(OLD.hora_salida,  '%Y-%m-%d %H:%i:%s'),
            'induccion_requerida', OLD.induccion_requerida,
            'induccion_fecha',   DATE_FORMAT(OLD.induccion_fecha, '%Y-%m-%d %H:%i:%s'),
            'observacion',       OLD.observacion
        ),
        JSON_OBJECT(
            'sede_id',           NEW.sede_id,
            'user_id',           NEW.user_id,
            'nombre',            NEW.nombre,
            'cedula',            NEW.cedula,
            'telefono',          NEW.telefono,
            'eps',               NEW.eps,
            'arl',               NEW.arl,
            'empresa',           NEW.empresa,
            'placa',             NEW.placa,
            'persona_visita',    NEW.persona_visita,
            'hora_entrada',      DATE_FORMAT(NEW.hora_entrada, '%Y-%m-%d %H:%i:%s'),
            'hora_salida',       DATE_FORMAT(NEW.hora_salida,  '%Y-%m-%d %H:%i:%s'),
            'induccion_requerida', NEW.induccion_requerida,
            'induccion_fecha',   DATE_FORMAT(NEW.induccion_fecha, '%Y-%m-%d %H:%i:%s'),
            'observacion',       NEW.observacion
        ),
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;


-- 3. Trigger DELETE
DROP TRIGGER IF EXISTS `trg_visitantes_delete`;

DELIMITER $$

CREATE TRIGGER `trg_visitantes_delete`
AFTER DELETE ON `tbl_visitantes`
FOR EACH ROW
BEGIN
    INSERT INTO `tbl_visitantes_log`
        (`visitante_id`, `evento`, `anterior`, `nuevo`, `user_id`, `created_at`)
    VALUES (
        OLD.id,
        'DELETE',
        JSON_OBJECT(
            'sede_id',           OLD.sede_id,
            'user_id',           OLD.user_id,
            'nombre',            OLD.nombre,
            'cedula',            OLD.cedula,
            'telefono',          OLD.telefono,
            'eps',               OLD.eps,
            'arl',               OLD.arl,
            'empresa',           OLD.empresa,
            'placa',             OLD.placa,
            'persona_visita',    OLD.persona_visita,
            'hora_entrada',      DATE_FORMAT(OLD.hora_entrada, '%Y-%m-%d %H:%i:%s'),
            'hora_salida',       DATE_FORMAT(OLD.hora_salida,  '%Y-%m-%d %H:%i:%s'),
            'induccion_requerida', OLD.induccion_requerida,
            'induccion_fecha',   DATE_FORMAT(OLD.induccion_fecha, '%Y-%m-%d %H:%i:%s'),
            'observacion',       OLD.observacion
        ),
        NULL,
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;
