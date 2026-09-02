-- Tabla: users_imagenes
-- Descripción: Foto de perfil del empleado (base64 completo + thumbnail 150x150)
-- BD: biometricip (central)
-- Fecha: 2026-09-02

CREATE TABLE `users_imagenes` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          BIGINT UNSIGNED NOT NULL,
  `imagen_base64`    LONGTEXT        NULL COMMENT 'Imagen completa redimensionada a 400x400 en base64',
  `imagen_thumbnail` LONGTEXT        NULL COMMENT 'Thumbnail 150x150 en base64 para mostrar en tabla',
  `created_at`       TIMESTAMP       NULL,
  `updated_at`       TIMESTAMP       NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_imagenes_user` (`user_id`),
  CONSTRAINT `fk_users_imagenes_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('users_imagenes', 'Foto de perfil del empleado (base64 + thumbnail)', 1, 0, 0, 1, 200, NOW(), NOW());
