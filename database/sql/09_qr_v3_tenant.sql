-- ============================================================
-- BiometricIP — QR v3 (Web público)
-- Ejecutar sobre cada BD tenant: biometricip_1, etc.
-- ============================================================

-- 1. Agregar columna qr_v3_token a tbl_sedes
--    Se ubica después de qr_static_token si existe; de lo contrario al final.
ALTER TABLE `tbl_sedes`
    ADD COLUMN `qr_v3_token` VARCHAR(64) NULL;

-- 2. Agregar 'qr_web' al ENUM metodo de tbl_registros_asistencia
ALTER TABLE `tbl_registros_asistencia`
    MODIFY COLUMN `metodo` ENUM('qr','biometrico','reconocimiento_facial','foto','qr_web') NOT NULL;

-- 3. Crear tabla de visitantes
--    Un registro por visita: hora_entrada siempre presente, hora_salida se actualiza al salir.
CREATE TABLE IF NOT EXISTS `tbl_visitantes` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sede_id`          BIGINT UNSIGNED NOT NULL,
    `nombre`           VARCHAR(255)    NOT NULL,
    `cedula`           VARCHAR(20)     NOT NULL,
    `telefono`         VARCHAR(20)     NULL DEFAULT NULL,
    `eps`              VARCHAR(100)    NULL DEFAULT NULL,
    `arl`              VARCHAR(100)    NULL DEFAULT NULL,
    `persona_visita`   VARCHAR(255)    NOT NULL,
    `hora_entrada`     DATETIME        NOT NULL,
    `hora_salida`      DATETIME        NULL DEFAULT NULL,
    `created_at`       TIMESTAMP       NULL,
    `updated_at`       TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_visitantes_sede`   (`sede_id`),
    INDEX `idx_visitantes_cedula` (`cedula`),
    INDEX `idx_visitantes_entrada`(`hora_entrada`),
    CONSTRAINT `fk_visitantes_sede`
        FOREIGN KEY (`sede_id`) REFERENCES `tbl_sedes` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Crear tabla de imágenes de visitantes
--    tipo='entrada' = foto al llegar, tipo='salida' = foto al salir
CREATE TABLE IF NOT EXISTS `tbl_visitantes_imagenes` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `visitante_id`     BIGINT UNSIGNED NOT NULL,
    `tipo`             ENUM('entrada','salida') NOT NULL,
    `foto_base64`      LONGTEXT        NOT NULL,
    `thumbnail_base64` TEXT            NOT NULL,
    `created_at`       TIMESTAMP       NULL,
    `updated_at`       TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_visitantes_img_visitante` (`visitante_id`),
    CONSTRAINT `fk_visitantes_imagenes_visitante`
        FOREIGN KEY (`visitante_id`) REFERENCES `tbl_visitantes` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
