-- Mueve duracion_almuerzo_min de tbl_horarios a tbl_horario_dias

-- 1. Agregar columna en el detalle
ALTER TABLE tbl_horario_dias
    ADD COLUMN duracion_almuerzo_min SMALLINT UNSIGNED NULL DEFAULT NULL
        COMMENT 'Minutos de almuerzo a descontar ese día. NULL = no aplica.'
        AFTER hora_salida;

-- 2. Migrar el valor actual del encabezado a cada día
UPDATE tbl_horario_dias d
JOIN tbl_horarios h ON h.id = d.horario_id
SET d.duracion_almuerzo_min = h.duracion_almuerzo_min
WHERE h.duracion_almuerzo_min IS NOT NULL;

-- 3. Eliminar columna del encabezado
ALTER TABLE tbl_horarios DROP COLUMN IF EXISTS duracion_almuerzo_min;
