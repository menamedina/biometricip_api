-- Agrega el campo retardo_min a tbl_horarios
-- Minutos de tolerancia para marcar tardanza. 0 = sin tolerancia (default).
ALTER TABLE tbl_horarios
    ADD COLUMN retardo_min SMALLINT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Minutos de tolerancia para marcar tardanza. 0 = sin tolerancia.'
        AFTER duracion_almuerzo_min;
