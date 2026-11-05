-- ============================================================
-- Script: 056_add_dia_ejecucion_turno_to_empresas.sql
-- Descripcion: Agrega el dia de ejecucion automatica de la
--              programacion de turno a tbl_empresas.
--              0=Domingo 1=Lunes 2=Martes 3=Miercoles
--              4=Jueves  5=Viernes 6=Sabado
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

ALTER TABLE `tbl_empresas`
    ADD COLUMN `dia_ejecucion_turno` TINYINT UNSIGNED NULL DEFAULT 2
    COMMENT 'Dia de la semana para aplicar la programacion de turno (0=Dom,1=Lun,2=Mar,3=Mie,4=Jue,5=Vie,6=Sab)';
