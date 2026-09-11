-- ============================================================
-- Script: 029_create_tbl_login_images.sql
-- Descripcion: Tabla de imagenes para el carrusel del login
--              Soporta imagenes globales o por empresa
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

CREATE TABLE IF NOT EXISTS `tbl_login_images` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `empresa_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'NULL = imagen global, ID = solo esa empresa',
    `titulo`     VARCHAR(150)    NULL DEFAULT NULL,
    `imagen`     VARCHAR(500)    NOT NULL COMMENT 'Ruta relativa en storage',
    `orden`      INT UNSIGNED    NOT NULL DEFAULT 0,
    `activo`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_empresa_activo` (`empresa_id`, `activo`),
    CONSTRAINT `fk_login_images_empresa`
        FOREIGN KEY (`empresa_id`) REFERENCES `tbl_empresas`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrar en tbl_admin_tenant
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('tbl_login_images', 'Imagenes del carrusel de login (globales o por empresa)', 1, 0, 0, 1, 100, NOW(), NOW());
