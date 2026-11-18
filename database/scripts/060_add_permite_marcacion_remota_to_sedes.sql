-- Agrega soporte de marcación remota a sedes
-- permite_marcacion_remota: si es 1, los usuarios con marcacion_global=1 pueden marcar sin geocerca
-- lat, lng, radio_mts pasan a nullable (sedes remotas no tienen ubicación física)

ALTER TABLE `tbl_sedes`
    ADD COLUMN `permite_marcacion_remota` TINYINT(1) NOT NULL DEFAULT 0 AFTER `doble_registro`,
    MODIFY COLUMN `lat`       DECIMAL(10,7) NULL DEFAULT NULL,
    MODIFY COLUMN `lng`       DECIMAL(10,7) NULL DEFAULT NULL,
    MODIFY COLUMN `radio_mts` INT(11)       NULL DEFAULT NULL;
