-- ============================================================
-- BiometricIP — Audit log de registros de asistencia
-- Triggers: INSERT (solo manual), UPDATE, DELETE
-- El usuario se pasa desde la app con: SET @audit_user_id = ?;
-- Ejecutar sobre cada BD tenant: biometricip_2, etc.
-- ============================================================

USE biometricip_2;

-- 1. Tabla de log
CREATE TABLE IF NOT EXISTS `tbl_registros_asistencia_log` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `registro_id`  BIGINT UNSIGNED NOT NULL,
    `evento`       ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    `anterior`     JSON NULL,
    `nuevo`        JSON NULL,
    `user_id`      INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_ralog_registro` (`registro_id`),
    INDEX `idx_ralog_evento`   (`evento`),
    INDEX `idx_ralog_fecha`    (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. Trigger INSERT (solo registros manuales)
DROP TRIGGER IF EXISTS `trg_registros_asistencia_insert`;

DELIMITER $$

CREATE TRIGGER `trg_registros_asistencia_insert`
AFTER INSERT ON `tbl_registros_asistencia`
FOR EACH ROW
BEGIN
    IF NEW.metodo = 'manual' THEN
        INSERT INTO `tbl_registros_asistencia_log`
            (`registro_id`, `evento`, `anterior`, `nuevo`, `user_id`, `created_at`)
        VALUES (
            NEW.id,
            'INSERT',
            NULL,
            JSON_OBJECT(
                'user_id',               NEW.user_id,
                'sede_id',               NEW.sede_id,
                'horario_id',            NEW.horario_id,
                'tipo',                  NEW.tipo,
                'metodo',                NEW.metodo,
                'fecha_hora',            DATE_FORMAT(NEW.fecha_hora, '%Y-%m-%d %H:%i:%s'),
                'observacion',           NEW.observacion,
                'qr_validado',           NEW.qr_validado,
                'geocerca_validada',     NEW.geocerca_validada,
                'distancia_oficina_mts', NEW.distancia_oficina_mts
            ),
            IFNULL(@audit_user_id, 0),
            NOW()
        );
    END IF;
END$$

DELIMITER ;


-- 3. Trigger UPDATE
DROP TRIGGER IF EXISTS `trg_registros_asistencia_update`;

DELIMITER $$

CREATE TRIGGER `trg_registros_asistencia_update`
AFTER UPDATE ON `tbl_registros_asistencia`
FOR EACH ROW
BEGIN
    INSERT INTO `tbl_registros_asistencia_log`
        (`registro_id`, `evento`, `anterior`, `nuevo`, `user_id`, `created_at`)
    VALUES (
        OLD.id,
        'UPDATE',
        JSON_OBJECT(
            'user_id',               OLD.user_id,
            'sede_id',               OLD.sede_id,
            'horario_id',            OLD.horario_id,
            'tipo',                  OLD.tipo,
            'metodo',                OLD.metodo,
            'fecha_hora',            DATE_FORMAT(OLD.fecha_hora, '%Y-%m-%d %H:%i:%s'),
            'observacion',           OLD.observacion,
            'qr_validado',           OLD.qr_validado,
            'geocerca_validada',     OLD.geocerca_validada,
            'distancia_oficina_mts', OLD.distancia_oficina_mts
        ),
        JSON_OBJECT(
            'user_id',               NEW.user_id,
            'sede_id',               NEW.sede_id,
            'horario_id',            NEW.horario_id,
            'tipo',                  NEW.tipo,
            'metodo',                NEW.metodo,
            'fecha_hora',            DATE_FORMAT(NEW.fecha_hora, '%Y-%m-%d %H:%i:%s'),
            'observacion',           NEW.observacion,
            'qr_validado',           NEW.qr_validado,
            'geocerca_validada',     NEW.geocerca_validada,
            'distancia_oficina_mts', NEW.distancia_oficina_mts
        ),
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;


-- 4. Trigger DELETE
DROP TRIGGER IF EXISTS `trg_registros_asistencia_delete`;

DELIMITER $$

CREATE TRIGGER `trg_registros_asistencia_delete`
AFTER DELETE ON `tbl_registros_asistencia`
FOR EACH ROW
BEGIN
    INSERT INTO `tbl_registros_asistencia_log`
        (`registro_id`, `evento`, `anterior`, `nuevo`, `user_id`, `created_at`)
    VALUES (
        OLD.id,
        'DELETE',
        JSON_OBJECT(
            'user_id',               OLD.user_id,
            'sede_id',               OLD.sede_id,
            'horario_id',            OLD.horario_id,
            'tipo',                  OLD.tipo,
            'metodo',                OLD.metodo,
            'fecha_hora',            DATE_FORMAT(OLD.fecha_hora, '%Y-%m-%d %H:%i:%s'),
            'observacion',           OLD.observacion,
            'qr_validado',           OLD.qr_validado,
            'geocerca_validada',     OLD.geocerca_validada,
            'distancia_oficina_mts', OLD.distancia_oficina_mts
        ),
        NULL,
        IFNULL(@audit_user_id, 0),
        NOW()
    );
END$$

DELIMITER ;


INSERT INTO `tbl_admin_tenant`
      (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
  VALUES
      ('tbl_horario_dias',              'Días y horas por horario laboral (cabecera-detalle)', 0, 1, 0, 1, 41, NOW(), NOW()),
      ('tbl_registros_asistencia_log',  'Auditoría de cambios en registros de asistencia',    0, 1, 0, 1, 21, NOW(), NOW());
