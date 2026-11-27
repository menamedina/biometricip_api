-- Agregar columna thumbnail_path a tbl_fotos_asistencia
-- y liberar las columnas base64 (foto_base64 ya fue limpiada, thumbnail_base64 migra a disco)

-- 1. Permitir NULL en columnas base64 existentes
ALTER TABLE tbl_fotos_asistencia
    MODIFY COLUMN foto_base64      LONGTEXT NULL,
    MODIFY COLUMN thumbnail_base64 TEXT     NULL;

-- 2. Agregar columna de ruta del archivo en disco
ALTER TABLE tbl_fotos_asistencia
    ADD COLUMN thumbnail_path VARCHAR(500) NULL AFTER thumbnail_base64;
