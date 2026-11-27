-- Agregar columna thumbnail_path a tbl_visitantes_imagenes
-- y liberar las columnas base64 (misma lógica que tbl_fotos_asistencia)

-- 1. Permitir NULL en columnas base64 existentes
--    (foto_base64 ya fue alterada en script 061, se incluye por seguridad)
ALTER TABLE tbl_visitantes_imagenes
    MODIFY COLUMN foto_base64      LONGTEXT NULL,
    MODIFY COLUMN thumbnail_base64 TEXT     NULL;

-- 2. Agregar columna de ruta del archivo en disco
ALTER TABLE tbl_visitantes_imagenes
    ADD COLUMN thumbnail_path VARCHAR(500) NULL AFTER thumbnail_base64;
