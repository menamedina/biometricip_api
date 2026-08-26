-- Actualiza la estructura de horarios en la BD tenant
-- Ejecutar en: biometricip_2 (o la BD tenant correspondiente)

-- 1. Crear tabla de días
CREATE TABLE IF NOT EXISTS tbl_horario_dias (
    id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    horario_id   BIGINT UNSIGNED  NOT NULL,
    dia_semana   TINYINT UNSIGNED NOT NULL COMMENT '1=Lun 2=Mar 3=Mié 4=Jue 5=Vie 6=Sáb 7=Dom',
    hora_entrada          TIME             NULL     COMMENT 'NULL = día no laboral',
    hora_salida           TIME             NULL,
    duracion_almuerzo_min SMALLINT UNSIGNED NULL     DEFAULT NULL,
    retardo_min           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_horario_dia (horario_id, dia_semana),
    CONSTRAINT fk_horario_dias_horario FOREIGN KEY (horario_id) REFERENCES tbl_horarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrar datos existentes (Lun-Vie con las horas actuales)
INSERT INTO tbl_horario_dias (horario_id, dia_semana, hora_entrada, hora_salida, retardo_min)
SELECT h.id, d.n, h.hora_entrada, h.hora_salida, 0
FROM tbl_horarios h
CROSS JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) d
WHERE h.hora_entrada IS NOT NULL;

-- 3. Eliminar columnas viejas de tbl_horarios
ALTER TABLE tbl_horarios DROP COLUMN IF EXISTS hora_entrada;
ALTER TABLE tbl_horarios DROP COLUMN IF EXISTS hora_salida;
ALTER TABLE tbl_horarios DROP COLUMN IF EXISTS retardo_min;
ALTER TABLE tbl_horarios DROP COLUMN IF EXISTS duracion_almuerzo_min;
