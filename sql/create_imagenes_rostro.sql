-- Crea la tabla de imágenes de rostro por usuario (BD del TENANT, ej: biometricip_1)
-- Almacena thumbnails 400x400 en base64 + descriptor facial (vector 128D)
-- Máximo 5 imágenes por usuario (validado en backend)
-- IMPORTANTE: Ejecutar en CADA base de datos de tenant (biometricip_1, biometricip_2, etc.)

CREATE TABLE tbl_imagenes_rostro_usuario (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT UNSIGNED NOT NULL,
    imagen_base64  TEXT            NOT NULL,   -- thumbnail 400x400 en base64 (generado por GD en backend)
    descriptor     JSON            NULL,        -- vector de características 128D generado en Flutter con ML Kit
    orden          TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
