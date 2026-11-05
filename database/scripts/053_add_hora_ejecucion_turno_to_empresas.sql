-- ============================================================
-- Script: 053_add_hora_ejecucion_turno_to_empresas.sql
-- Descripcion: Agrega el parametro de hora de ejecucion
--              automatica de programacion de turno a tbl_empresas.
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

ALTER TABLE `tbl_empresas`
    ADD COLUMN `hora_ejecucion_turno` TIME NULL DEFAULT '06:00:00'
    COMMENT 'Hora del martes para aplicar automaticamente la programacion de turno';
