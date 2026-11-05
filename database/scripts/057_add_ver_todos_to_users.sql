-- ============================================================
-- Script: 057_add_ver_todos_to_users.sql
-- Descripcion: Agrega el campo ver_todos a users. Cuando es 1
--              el usuario ve todos los empleados de su empresa
--              sin restriccion de lider_id (ej: jefe de nomina).
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

ALTER TABLE `users`
    ADD COLUMN `ver_todos` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'Si es 1 ve todos los empleados de la empresa sin filtro de lider_id';
