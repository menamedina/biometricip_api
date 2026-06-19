-- ============================================================
-- BiometricIP — Dispositivos Biométricos ZKTeco
-- Ejecutar sobre la BD tenant: biometricip_tenant_{empresa_id}
--
-- Este script crea las tablas necesarias para la integración
-- con dispositivos biométricos ZKTeco (MB160, etc.)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Tabla: tbl_dispositivos_biometricos
-- Almacena los dispositivos ZKTeco registrados por sede
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_dispositivos_biometricos` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sede_id`         BIGINT UNSIGNED NOT NULL,
    `nombre`          VARCHAR(255)    NOT NULL,
    `ip`              VARCHAR(45)     NOT NULL,
    `puerto`          INT             NOT NULL DEFAULT 4370,
    `numero_serie`    VARCHAR(100)    NULL,
    `modelo`          VARCHAR(100)    NULL,
    `plataforma`      VARCHAR(100)    NULL,
    `firmware`        VARCHAR(100)    NULL,
    `is_active`       TINYINT(1)      NOT NULL DEFAULT 1,
    `ultima_sync`     TIMESTAMP       NULL,
    `created_at`      TIMESTAMP       NULL,
    `updated_at`      TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ip_puerto` (`ip`, `puerto`),
    CONSTRAINT `fk_dispositivos_sede`
        FOREIGN KEY (`sede_id`) REFERENCES `tbl_sedes` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: tbl_sync_logs
-- Registro de cada sincronización realizada
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_sync_logs` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `dispositivo_id`  BIGINT UNSIGNED NOT NULL,
    `registros_nuevos` INT            NOT NULL DEFAULT 0,
    `registros_total`  INT            NOT NULL DEFAULT 0,
    `status`          ENUM('ok','error') NOT NULL DEFAULT 'ok',
    `mensaje`         TEXT            NULL,
    `created_at`      TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_dispositivo_id` (`dispositivo_id`),
    CONSTRAINT `fk_sync_dispositivo`
        FOREIGN KEY (`dispositivo_id`) REFERENCES `tbl_dispositivos_biometricos` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Agregar 'dispositivo' como método válido de asistencia
-- y campo para vincular el registro con el dispositivo origen
-- ------------------------------------------------------------
ALTER TABLE `tbl_registros_asistencia`
    MODIFY COLUMN `metodo` ENUM('qr','biometrico','reconocimiento_facial','foto','manual','dispositivo') NOT NULL,
    ADD COLUMN `dispositivo_id` BIGINT UNSIGNED NULL AFTER `notas`,
    ADD COLUMN `uid_dispositivo` VARCHAR(50) NULL AFTER `dispositivo_id`;

SET FOREIGN_KEY_CHECKS = 1;
