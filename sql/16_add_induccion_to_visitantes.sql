-- Agrega campos de inducción a tbl_visitantes (tenant DB)
-- induccion_requerida: 1 = requiere inducción (primera vez o > 12 meses)
-- induccion_fecha: cuándo se realizó la inducción en esta visita

ALTER TABLE tbl_visitantes
    ADD COLUMN induccion_requerida TINYINT(1) NOT NULL DEFAULT 0 AFTER placa,
    ADD COLUMN induccion_fecha DATETIME NULL DEFAULT NULL AFTER induccion_requerida;
