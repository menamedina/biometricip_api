-- ============================================================
-- Script: 030_remove_empresa_from_login_images.sql
-- Descripcion: Elimina empresa_id de tbl_login_images
--              Las imagenes del login seran siempre globales
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

ALTER TABLE `tbl_login_images` DROP FOREIGN KEY `fk_login_images_empresa`;
ALTER TABLE `tbl_login_images` DROP INDEX `idx_empresa_activo`;
ALTER TABLE `tbl_login_images` DROP COLUMN `empresa_id`;
ALTER TABLE `tbl_login_images` ADD INDEX `idx_activo` (`activo`);
