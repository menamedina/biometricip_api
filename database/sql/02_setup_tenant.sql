-- ============================================================
-- BiometricIP — BD Tenant
-- Ejecutar sobre la BD: biometricip_tenant_{empresa_id}
-- Ejemplo: USE biometricip_tenant_1;
--
-- Este script es ejecutado automáticamente por:
--   php artisan tenant:create-structure {empresa_id}
--
-- Para ejecutarlo manualmente:
--   1. Crear la BD: CREATE DATABASE biometricip_tenant_1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   2. Seleccionar: USE biometricip_tenant_1;
--   3. Ejecutar este script
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Tabla: tbl_sedes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_sedes` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo`     VARCHAR(255)    NOT NULL UNIQUE,
    `nombre`     VARCHAR(255)    NOT NULL,
    `direccion`  VARCHAR(255)    NULL,
    `lat`        DECIMAL(10,7)   NOT NULL,
    `lng`        DECIMAL(10,7)   NOT NULL,
    `radio_mts`  INT             NOT NULL DEFAULT 150,
    `secret_key` VARCHAR(255)    NOT NULL,
    `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP       NULL,
    `updated_at` TIMESTAMP       NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: tbl_registros_asistencia
-- Nota: user_id referencia a users en la BD central (biometricip).
--       No se usa FK de BD cruzada — se mantiene como referencia lógica.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_registros_asistencia` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`               BIGINT UNSIGNED NOT NULL,
    `sede_id`               BIGINT UNSIGNED NOT NULL,
    `tipo`                  ENUM('entrada','salida_almuerzo','regreso_almuerzo','salida') NOT NULL,
    `lat`                   DECIMAL(10,7)   NULL,
    `lng`                   DECIMAL(10,7)   NULL,
    `foto_evidencia`        VARCHAR(255)    NULL,
    `metodo`                ENUM('qr','biometrico','reconocimiento_facial','foto') NOT NULL,
    `qr_validado`           TINYINT(1)      NOT NULL DEFAULT 0,
    `geocerca_validada`     TINYINT(1)      NOT NULL DEFAULT 0,
    `distancia_oficina_mts` DECIMAL(8,2)    NULL,
    `notas`                 TEXT            NULL,
    `fecha_hora`            TIMESTAMP       NOT NULL,
    `created_at`            TIMESTAMP       NULL,
    `updated_at`            TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    CONSTRAINT `fk_registros_sede`
        FOREIGN KEY (`sede_id`) REFERENCES `tbl_sedes` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: tbl_fotos_asistencia
-- Las fotos se almacenan en Base64 (foto completa + thumbnail 40x40)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tbl_fotos_asistencia` (
    `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `attendance_record_id` BIGINT UNSIGNED NOT NULL,
    `foto_base64`          LONGTEXT        NOT NULL,
    `thumbnail_base64`     TEXT            NOT NULL,
    `created_at`           TIMESTAMP       NULL,
    `updated_at`           TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_fotos_registro`
        FOREIGN KEY (`attendance_record_id`) REFERENCES `tbl_registros_asistencia` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
